<?php

namespace Krak\DoctrineOAuth2\Model;

use League\OAuth2\Server\{
    Entities\AccessTokenEntityInterface,
    Entities\ClientEntityInterface,
    Entities\RefreshTokenEntityInterface,
    Repositories\AccessTokenRepositoryInterface,
    Repositories\RefreshTokenRepositoryInterface
};
use Doctrine\ORM\EntityManagerInterface;

class TokenRepository implements AccessTokenRepositoryInterface, RefreshTokenRepositoryInterface
{
    private $em;
    private $persist;
    private $revoke;

    public function __construct(EntityManagerInterface $em, $persist = true, $revoke = true) {
        $this->em = $em;
        $this->persist = $persist;
        $this->revoke = $revoke;
    }

    public function getNewToken(ClientEntityInterface $client, array $scopes, $user_id = null) {
        return new AccessToken();
    }

    public function getNewRefreshToken() {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refresh_token) {
        $this->persistToken($refresh_token);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $access_token) {
        $this->persistToken($access_token);
    }

    public function revokeRefreshToken($token_id) {
        $this->revokeToken($token_id, 'oauth2_refresh_tokens');
    }

    public function isRefreshTokenRevoked($token_id) {
        return $this->isTokenRevoked($token_id, 'oauth2_refresh_tokens');
    }

    public function revokeAccessToken($token_id) {
        $this->revokeToken($token_id, 'oauth2_access_tokens');
    }

    public function isAccessTokenRevoked($token_id) {
        return $this->isTokenRevoked($token_id, 'oauth2_access_tokens');
    }

    private function persistToken($token) {
        if (!$this->persist) {
            return;
        }

        $this->em->persist($token);
        $this->em->flush($token);
    }

    private function revokeToken($token_id, $table) {
        if (!$this->revoke) {
            return;
        }

        $conn = $this->em->getConnection();
        $conn->executeQuery("UPDATE $table SET is_revoked = 1 WHERE id = ?", [$token_id]);
    }

    private function isTokenRevoked($token_id, $table) {
        if (!$this->revoke) {
            return false;
        }

        $conn = $this->em->getConnection();
        return (bool) $conn->fetchColumn("SELECT is_revoked FROM $table WHERE id = ?", [$token_id]);
    }
}
