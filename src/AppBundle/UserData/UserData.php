<?php
namespace AppBundle\UserData;

use AppBundle\Entity\MeasurementType;
use Doctrine\DBAL\Connection;

/**
 * Class UserData
 * @package AppBundle\UserData
 */
class UserData
{
    /**
     * @var \PDO
     */
    private $conn;
    /**
     * @var MeasurementType
     */
    private $measurementType;

    /**
     * @param Connection $conn
     * @param MeasurementType $measurementType
     */
    public function __construct(Connection $conn, MeasurementType $measurementType)
    {
        $this->conn = $conn;
        $this->measurementType = $measurementType;
    }

    /**
     * @param $measurementTypeSlug
     * @return array
     */
    public function getUserData($measurementTypeSlug)
    {
        $stmt = $this->conn->prepare("
            SELECT me.event_time, m.units
            FROM measurement_event me INNER JOIN measurement m
            ON me.id = m.measurement_event_id
            WHERE m.measurement_type_id = :measurementType
        ");
        $stmt->execute([':measurementType' => $this->measurementType->getMeasurementType($measurementTypeSlug)['id']]);

        return $stmt->fetchAll();
    }
} 
