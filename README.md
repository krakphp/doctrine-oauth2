# Doctrine OAuth2

This library provides OAuth2 integration for Doctrine and Laravel. It uses the `league/oauth2-server` package for all of the heavy lifting and the Doctrine ORM for the backend.

## Installation

Install with composer at `krak/doctrine-oauth2`

## Laravel Usage

```php
<?php

$app = new Laravel\Lumen\Application(__DIR__ . '/..');

$app->register(LaravelDoctrine\ORM\DoctrineServiceProvider::class);
$app->register(LaravelDoctrine\Migrations\MigrationsServiceProvider::class);
$app->register(Krak\DoctrineOAuth2\OAuth2ServiceProvider::class);

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
```


```php
<?php

// config/oauth2.php

return [
    'grants' => [
        'refresh_token',
        'password',
        'client_credentials',
        'authorization_code',
        'implicit'
    ],
    'client_credentials' => [
        'access_token_ttl' => new DateInterval('P1Y'),
    ],
    'access_token_ttl' => new DateInterval('PT2H'),
    'refresh_token_ttl' => new DateInterval('P2Y'),
    'private_key' => resource_path('oauth-private.key'),
    'public_key' => resource_path('oauth-public.key'),
];
```

You need to run `./artisan oauth2:generate-keys` to create the oauth keys. You can optionally run `./artisan oauth2:seed` to run any seeds you may have defined for the oauth2 package.
