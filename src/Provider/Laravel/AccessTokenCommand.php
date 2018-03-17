<?php

namespace Krak\DoctrineOAuth2\Provider\Laravel;

use Illuminate\Console\Command;
use Symfony\Component\Console\Logger\ConsoleLogger;
use ModernProducer\Api\Doctrine\LoadFixtures;

use Illuminate\Contracts\Http;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class AccessTokenCommand extends Command
{
    protected $signature = 'oauth2:access-token {client-id} {client-secret} {--s|scope=*} {--g|grant-type=client_credentials} {json?}';
    protected $description = 'Create an oauth2 access token';

    public function handle() {
        $app = $this->getLaravel();

        $grantType = $this->option('grant-type');
        $clientId = $this->argument('client-id');
        $clientSecret = $this->argument('client-secret');
        $scopes = $this->option('scope');
        $json = json_decode($this->argument('json') ?? '{}', true) ?? [];

        $app = $this->getLaravel();
        $uri = $app->make('config')->get('app.url') . '/oauth2/access-token';
        $kernel = $app->make(Http\Kernel::class);

        $body = array_merge([
            'grant_type' => $grantType,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ], array_filter([
            'scope' => implode(' ', $scopes)
        ]), $json);

        $symfonyRequest = SymfonyRequest::create($uri, 'POST', $body, [], [], [], '');

        $response = $kernel->handle($request = Request::createFromBase($symfonyRequest));

        $respContent = $response->getContent();
        $res = json_decode($respContent);
        if (!$res) {
            $this->error($respContent);
            return;
        }

        $this->info(json_encode($res, JSON_PRETTY_PRINT));
    }
}
