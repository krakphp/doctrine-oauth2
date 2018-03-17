<?php

namespace Krak\DoctrineOAuth2\Provider\Laravel;

use Krak\DoctrineOAuth2;
use Illuminate\Support\{ServiceProvider, Collection};
use League\OAuth2\Server\{
    AuthorizationServer,
    Exception\OAuthServerException,
    Grant\ClientCredentialsGrant,
    Grant\PasswordGrant,
    Grant\RefreshTokenGrant,
    Repositories\AccessTokenRepositoryInterface,
    Repositories\ClientRepositoryInterface,
    Repositories\RefreshTokenRepositoryInterface,
    Repositories\ScopeRepositoryInterface,
    Repositories\UserRepositoryInterface,
    ResourceServer
};
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\Mapping\Driver\{
    PHPDriver,
    SymfonyFileLocator,
    MappingDriverChain
};
use Laravel\Lumen;

final class OAuth2ServiceProvider extends ServiceProvider
{
    public function register() {
        $this->commands([
            DoctrineOAuth2\Console\GenerateKeysCommand::class,
            DoctrineOAuth2\Console\SeedCommand::class,
            AccessTokenCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../../Resources/laravel/config.php' => $this->app->basePath('config/oauth2.php'),
        ], 'config');


        if ($this->app instanceof Lumen\Application) {
            $this->app->configure('oauth2');
            $this->app->routeMiddleware([
                'oauth2' => ResourceServerMiddleware::class
            ]);
        }

        $this->app->singleton(AccessTokenRepositoryInterface::class, DoctrineOAuth2\Model\TokenRepository::class);
        $this->app->singleton(ClientRepositoryInterface::class, DoctrineOAuth2\Model\ClientRepository::class);
        $this->app->singleton(RefreshTokenRepositoryInterface::class, DoctrineOAuth2\Model\TokenRepository::class);
        $this->app->singleton(ScopeRepositoryInterface::class, function($c) {
            return new DoctrineOAuth2\Model\ScopeRepository($c->get(EntityManagerInterface::class), $c->get('oauth2.finalizeScopes'));
        });
        $this->app->singleton(DoctrineOAuth2\Console\GenerateKeysCommand::class, function($app) {
            return new DoctrineOAuth2\Console\GenerateKeysCommand($app->resourcePath());
        });
        $this->app->singleton(DoctrineOAuth2\Console\SeedCommand::class, function($app) {
            return new DoctrineOAuth2\Console\SeedCommand($app);
        });
        $this->app->singleton('oauth2.finalizeScopes', function() {
            return;
        });

        $this->app->singleton(AuthorizationServer::class, function($c) {
            $oauthConfig = $c->get('config')->get('oauth2');

            $server = new AuthorizationServer(
                $c->get(ClientRepositoryInterface::class),
                $c->get(AccessTokenRepositoryInterface::class),
                $c->get(ScopeRepositoryInterface::class),
                $oauthConfig['private_key'],
                $oauthConfig['public_key']
            );

            $refreshTokenTTL = $oauthConfig['refresh_token_ttl'] ?? null;
            $accessTokenTTL = $oauthConfig['access_token_ttl'];

            foreach ($oauthConfig['grants'] as $grant) {
                switch ($grant) {
                case 'refresh_token':
                    $grant = new RefreshTokenGrant($c->get(RefreshTokenRepositoryInterface::class));
                    if ($refreshTokenTTL) {
                        $grant->setRefreshTokenTTL($refreshTokenTTL);
                    }
                    $server->enableGrantType($grant, $accessTokenTTL);
                    break;
                case 'password':
                    $grant = new PasswordGrant(
                        $c->get(UserRepositoryInterface::class),
                        $c->get(RefreshTokenRepositoryInterface::class)
                    );
                    if ($refreshTokenTTL) {
                        $grant->setRefreshTokenTTL($refreshTokenTTL);
                    }
                    $server->enableGrantType($grant, $accessTokenTTL);
                    break;
                case 'client_credentials':
                    $server->enableGrantType(new ClientCredentialsGrant(), $oauthConfig['client_credentials']['access_token_ttl'] ?? $accessTokenTTL);
                    break;
                case 'authorization_code': break;
                case 'implicit': break;
                }
            }

            return $server;
        });
        $this->app->singleton(ResourceServer::class, function($c) {
            return new ResourceServer(
                $c->get(AccessTokenRepositoryInterface::class),
                $c->get('config')->get('oauth2.public_key')
            );
        });

        $this->app->singleton('oauth2.seed', function($c) {
            return new DoctrineOAuth2\Model\Seed($c->get('em'), $c->get('oauth2.seeds')->toArray());
        });
        $this->app->alias('oauth2.seed', DoctrineOAuth2\Model\Seed::class);
        $this->app->instance('oauth2.seeds', new Collection());

        $this->app->extend('em', function($em, $c) {
            return $this->extendEntityManager($em, $c);
        });
        $this->app->resolving('doctrine.managers.default', function($em, $c) {
            return $this->extendEntityManager($em, $c);
        });

        $this->app->router->group(['prefix' => '/oauth2'], function($router) {
            $router->post('/access-token', function(ServerRequestInterface $req, AuthorizationServer $server) {
                try {
                    return $server->respondToAccessTokenRequest($req, new Response('php://memory', 200));
                } catch (OAuthServerException $exception) {
                    return $exception->generateHttpResponse(new Response('php://memory', 500));
                }
            });
        });
    }

    private function extendEntityManager($em, $c) {
        $config = $em->getConfiguration();
        $config->addEntityNamespace('OAuth2', DoctrineOAuth2\Model::class);
        $metaDriver = $config->getMetadataDriverImpl();
        $oauth2Driver = new PHPDriver(new SymfonyFileLocator([
            __DIR__ . '/../../Resources/doctrine-mapping' => DoctrineOAuth2\Model::class,
        ], '.php'));
        $chainDriver = new MappingDriverChain();
        $chainDriver->setDefaultDriver($metaDriver);
        $chainDriver->addDriver($oauth2Driver, DoctrineOAuth2\Model::class);
        $config->setMetadataDriverImpl($chainDriver);
        return $em;
    }
}
