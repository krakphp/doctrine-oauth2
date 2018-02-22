<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\{
    Repositories\ClientRepositoryInterface
};
use Doctrine\ORM\EntityManagerInterface;

class ClientRepository implements ClientRepositoryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function getClientEntity($clientId, $grantType = null, $clientSecret = null, $mustValidateSecret = true) {
        $client = $this->em->find(Client::class, $clientId);
        if (!$client) {
            return;
        }

        if (!$client->redirectUri && ($grantType == 'implicit' || $grantType == 'authorization_code')) {
            return;
        }

        if ($mustValidateSecret) {
            $res = $client->verifySecret($clientSecret);
            if (!$res) {
                return;
            }
        }

        return $client;
    }
}
