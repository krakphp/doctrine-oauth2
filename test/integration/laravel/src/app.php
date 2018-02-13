<?php

use Krak\DoctrineOAuth2\{
    Model\Scope,
    Model\Client,
    Provider\Laravel\OAuth2ServiceProvider
};

function loadEnv() {
    putenv("CACHE_DRIVER=array");
    putenv("SESSION_DRIVER=array");
    putenv("DB_CONNECTION=sqlite");
    putenv("APP_DEBUG=1");
}

function createApp() {
    $app = new Laravel\Lumen\Application(__DIR__ . '/..');
    $app->singleton(Illuminate\Contracts\Console\Kernel::class, Laravel\Lumen\Console\Kernel::class);
    $app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, Laravel\Lumen\Exceptions\Handler::class);
    $app->instance('path.config', $app->basePath('config'));
    $app->instance('path.public', $app->basePath('public'));
    $app->register(new class($app) extends Illuminate\Support\ServiceProvider {
        public function register() {
            $this->commands([Illuminate\Foundation\Console\VendorPublishCommand::class]);
            $this->commands([Illuminate\Foundation\Console\ServeCommand::class]);
        }
    });
    $app->register(LaravelDoctrine\ORM\DoctrineServiceProvider::class);
    $app->register(LaravelDoctrine\Migrations\MigrationsServiceProvider::class);
    $app->register(OAuth2ServiceProvider::class);

    $app['oauth2.seeds']->push(function($em, $logger) {
        $scopes = [
            new Scope('basic', 'Basic', 'Allows basic access to the API.'),
            new Scope('user', 'User', 'Allows user access to the API.'),
        ];

        $logger->info("Creating local client");
        $client = new Client('local', 'local', 'local123', '', $scopes);
        $em->persist($client);
        foreach ($scopes as $scope) {
            $logger->info("Creating scope: ". json_encode($scope, JSON_PRETTY_PRINT));
            $em->persist($scope);
        }
    });

    $app->router->group(['middleware' => 'oauth2'], function($router) {
        $router->get('/', function(Illuminate\Http\Request $req) {
            return $req->attributes->all();
        });
    });

    return $app;
}
