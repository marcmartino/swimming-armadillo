<?php
namespace AppBundle\Entity;

/**
 * Class Oauth_Access_Token
 * @package AppBundle\Entity
 */
class Oauth_Access_Token
{
    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @param Connection|\PDO $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

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
} 