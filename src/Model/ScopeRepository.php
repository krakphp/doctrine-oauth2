<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\{
    Entities\ClientEntityInterface,
    Repositories\ScopeRepositoryInterface
};
use Doctrine\ORM\EntityManagerInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    private $em;
    private $finalizeScopes;

    public function __construct(EntityManagerInterface $em, callable $finalizeScopes = null) {
        $this->em = $em;
        $this->finalizeScopes = $finalizeScopes;
    }

    public function getScopeEntityByIdentifier($id) {
        return $this->em->find(Scope::class, $id);
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $client,
        $userId = null
    ) {
        return $this->finalizeScopes ? ($this->finalizeScopes)($scopes, $grantType, $client, $userId) : $scopes;
    }
}
