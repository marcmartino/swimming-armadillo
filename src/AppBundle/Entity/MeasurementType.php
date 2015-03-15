<?php
namespace AppBundle\Entity;

use AppBundle\Exception\MeasurementTypeNotFoundException;

/**
 * Class MeasurementType
 * @package AppBundle\Entity
 */
class MeasurementType extends AbstractEntity
{
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
     * @return mixed
     * @throws MeasurementTypeNotFoundException
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

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->conn->query("SELECT * FROM measurement_types")->fetchAll();
    }
} 