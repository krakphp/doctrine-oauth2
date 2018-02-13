<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getIdentifier() {
        return $this->id;
    }
}
