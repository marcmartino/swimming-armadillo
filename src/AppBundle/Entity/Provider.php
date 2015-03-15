<?php
namespace AppBundle\Entity;

/**
 * Class Provider
 * @package AppBundle\Entity
 */
class Provider extends AbstractEntity
{
    /**
     * @return array
     */
    public function getProviders($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM service_providers sp
            LEFT JOIN oauth_access_tokens oat
            ON  sp.id = oat.service_provider_id AND oat.user_id = :userId
        ");
        $stmt->execute([
            ':userId' => $userId
        ]);
        return $stmt->fetchAll();
    }

    public function getProvider($slug)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM service_providers WHERE slug = :slug
        ");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetchAll();
    }
} 