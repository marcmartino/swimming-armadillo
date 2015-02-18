<?php
namespace AppBundle\Entity;

use Doctrine\DBAL\Connection;

/**
 * Class Measurement
 * @package AppBundle\Entity
 */
class Measurement
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
     * @param $measurementEventId
     * @param $measurementTypeId
     * @param $unitsTypeId
     * @param $units
     */
    public function store(
        $measurementEventId,
        $measurementTypeId,
        $unitsTypeId,
        $units
    ) {
        $measurementQuery = 'INSERT INTO measurement (measurement_event_id, measurement_type_id, units_type_id, units) VALUES (:measurement_event_id, :measurement_type_id, :units_type_id, :units)';
        $measurementStmt = $this->conn->prepare($measurementQuery);
        $measurementStmt->execute([
            ':measurement_event_id' => $measurementEventId,
            ':measurement_type_id' => $measurementTypeId,
            ':units_type_id' => $unitsTypeId,
            ':units' => $units
        ]);
    }
} 