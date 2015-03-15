<?php
namespace AppBundle\Entity;

/**
 * Class Measurement
 * @package AppBundle\Entity
 */
class Measurement extends AbstractEntity
{

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