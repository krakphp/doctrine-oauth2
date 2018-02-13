<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Client implements ClientEntityInterface
{
    public $id;
    public $name;
    public $secret;
    public $redirectUri;
    public $scopes;
    public $createdAt;

    public function __construct($id, $name, $secret, $redirectUri, array $scopes = []) {
        $this->id = $id;
        $this->name = $name;
        $this->secret = password_hash($secret, PASSWORD_BCRYPT);
        $this->redirectUri = $redirectUri;
        $this->scopes = new ArrayCollection($scopes);
        $this->createdAt = new \DateTime();
    }

    public function getIdentifier() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getRedirectUri() {
        return $this->redirectUri;
    }

    public function verifySecret($secret) {
        return password_verify($secret, $this->secret);
    }
}
