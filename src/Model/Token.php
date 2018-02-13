<?php

namespace Krak\DoctrineOAuth2\Model;

trait Token
{
    public $id;
    public $expiryDateTime;
    public $isRevoked;
    public $createdAt;

    private function initToken() {
        $this->isRevoked = false;
        $this->createdAt = new \DateTime();
    }

    public function getIdentifier() {
        return $this->id;
    }

    public function setIdentifier($id) {
        $this->id = $id;
    }

    public function getExpiryDateTime() {
        return $this->expiryDateTime;
    }

    public function setExpiryDateTime(\DateTime $expiryDateTime) {
        $this->expiryDateTime = $expiryDateTime;
    }
}
