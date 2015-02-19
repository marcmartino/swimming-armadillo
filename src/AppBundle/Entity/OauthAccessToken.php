<?php
namespace AppBundle\Entity;

/**
 * Class OAuthAccessToken
 * @package AppBundle\Entity
 */
class OAuthAccessToken extends AbstractEntity
{
    /**
     * Check to see if a user already has an access token stored for given service provider
     *
     * @param $userId
     * @param $providerId
     * @return bool
     */
    public function doesUserHaveProviderAccessToken($userId, $providerId)
    {
        foreach ($this->getUserOAuthAccessTokens($userId) as $accessToken) {
            if ($accessToken['service_provider_id'] === $providerId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getUserOAuthAccessTokens($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM oauth_access_tokens WHERE user_id = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * @param $userId
     * @param $providerId
     * @param $foreignUserId
     * @param $accessToken
     * @param $accessTokenSecret
     */
    public function store(
        $userId,
        $providerId,
        $foreignUserId,
        $accessToken,
        $accessTokenSecret
    ) {
        $stmt = $this->conn->prepare("
            INSERT INTO oauth_access_tokens (user_id, service_provider_id, foreign_user_id, token, secret)
            VALUES (:userId, :providerId, :foreignUserId, :accessToken, :accessTokenSecret)
        ");
        $stmt->execute([
            ':userId' => $userId,
            ':providerId' => $providerId,
            ':foreignUserId' => $foreignUserId,
            ':accessToken' => $accessToken,
            ':accessTokenSecret' => $accessTokenSecret,
        ]);
    }

    /**
     * @param $userId
     * @param $serviceProviderId
     * @return array
     */
    public function getOAuthAccessTokenForUserAndServiceProvider($userId, $serviceProviderId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM oauth_access_tokens
            WHERE user_id = :userId AND service_provider_id = :serviceProviderId
            ");
        $stmt->execute([':userId' => $userId, ':serviceProviderId' => $serviceProviderId]);

        return $stmt->fetchAll();
    }
} 