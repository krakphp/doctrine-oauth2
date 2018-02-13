<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\Entities\{
    ScopeEntityInterface,
    AccessTokenEntityInterface,
    ClientEntityInterface,
    Traits\AccessTokenTrait
};
use Doctrine\Common\Collections\ArrayCollection;

class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait;
    use Token;

    public $userId;
    public $client;
    public $scopes;

    public function __construct() {
        $this->scopes = new ArrayCollection();
        $this->initToken();
    }

    public function setUserIdentifier($userId) {
        $this->userId = $userId;
    }

    public function getUserIdentifier() {
        return $this->userId;
    }

    public function getClient() {
        return $this->client;
    }

    public function setClient(ClientEntityInterface $client) {
        $this->client = $client;
    }

    public function addScope(ScopeEntityInterface $scope) {
        $this->scopes->add($scope);
    }

    public function getScopes() {
        return $this->scopes->getValues();
    }
}
