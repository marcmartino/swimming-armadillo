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
     * @param $measurementTypeId
     * @param \DateTime $startDatetime
     * @param \DateTime $endDatetime
     * @return array
     * @throws \AppBundle\Exception\MeasurementTypeNotFoundException
     */
    public function getUserData($measurementTypeId, $startDatetime = null, $endDatetime = null)
    {
        $query = "SELECT me.event_time, m.units
            FROM measurementevent me INNER JOIN measurement m
            ON me.id = m.measurement_event_id
            WHERE m.measurement_type_id = :measurementType";

        $parameters = [
            ':measurementType' => $measurementTypeId,
        ];
        if (!empty($startDatetime)) {
            $query .= " AND me.event_time > :startDatetime";
            $parameters[':startDatetime'] = $startDatetime->format('Y-m-d H:i:s');
        }
        if (!empty($endDatetime)) {
            $query .= " AND me.event_time < :endDatetime";
            $parameters[':endDatetime'] = $endDatetime->format('Y-m-d H:i:s');
        }

        $query .= " ORDER BY me.event_time";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }
} 
