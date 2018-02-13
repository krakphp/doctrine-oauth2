<?php

namespace Krak\DoctrineOAuth2\Provider\Laravel;

use League\OAuth2\Server\{ResourceServer, Exception\OAuthServerException};
use Zend\Diactoros\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\{DiactorosFactory, HttpFoundationFactory};

final class ResourceServerMiddleware
{
    private $server;

    public function __construct(ResourceServer $server) {
        $this->server = $server;
    }

    public function handle($req, callable $next) {
        $df = new DiactorosFactory();
        $hf = new HttpFoundationFactory();

        $psrReq = $df->createRequest($req);

        try {
            $psrReq = $this->server->validateAuthenticatedRequest($psrReq);
        } catch (OAuthServerException $exception) {
            return $hf->createResponse($exception->generateHttpResponse(new Response('php://memory', 500)));
        }

        $req->attributes->add($psrReq->getAttributes());
        return $next($req);
    }
}
