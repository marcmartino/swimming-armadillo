<?php
namespace AppBundle\Entity;

use Doctrine\DBAL\Driver\Connection;
use AppBundle\Exception\MeasurementTypeNotFoundException;

/**
 * Class MeasurementType
 * @package AppBundle\Entity
 */
class MeasurementType
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
     * @return array
     */
    public function get($userId)
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

    /**
     * @param $slug
     * @return array
     */
    public function getMeasurementType($slug)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM measurement_types WHERE slug = :slug
        ");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->rowCount() == 0) {
            throw new MeasurementTypeNotFoundException("Measurement type '$slug' not found");
        }
        return $stmt->fetch();
    }
} 