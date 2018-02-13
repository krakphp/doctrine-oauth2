<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\Entities\{
    RefreshTokenEntityInterface,
    AccessTokenEntityInterface
};

class RefreshToken implements RefreshTokenEntityInterface
{
    use Token;

    public $accessToken;

    public function __construct() {
        $this->initToken();
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function setAccessToken(AccessTokenEntityInterface $accessToken) {
        $this->accessToken = $accessToken;
    }
}
