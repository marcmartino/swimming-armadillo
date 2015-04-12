<?php

namespace AppBundle\Insight;

use DateTime;
use Doctrine\DBAL\Driver\Connection;

abstract class AbstractInsight {
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @param Connection|\PDO $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Returns the first measurement of a given type after a certain date
     *
     * @param DateTime $date
     * @param $measurementTypeId
     * @return mixed
     */
    public function gitFirstMeasurementOfType(DateTime $date, $measurementTypeId)
    {
        $stmt = $this->conn->prepare("SELECT units FROM measurement m INNER JOIN measurement_event me ON
        m.measurement_event_id = me.id WHERE me.event_time > :startDate AND m.measurement_type_id = :measurementTypeId ORDER BY me.event_time ASC
        LIMIT 1");

        $stmt->execute([
            ':startDate' => $date->format('Y-m-d H:i:s'),
            ':measurementTypeId' => $measurementTypeId
        ]);

        return $firstMeasurement = $stmt->fetch();
    }

    /**
     * Returns the last measurement of a given type before a certain date
     *
     * @param DateTime $date
     * @param $measurementTypeId
     * @return mixed
     */
    public function getLastMeasurementOfType(DateTime $date, $measurementTypeId)
    {
        $stmt = $this->conn->prepare("SELECT units FROM measurement m INNER JOIN measurement_event me ON
        m.measurement_event_id = me.id WHERE me.event_time < :endDate AND m.measurement_type_id = :measurementTypeId ORDER BY me.event_time DESC
        LIMIT 1");

        $stmt->execute([
            ':endDate' => $date->format('Y-m-d H:i:s'),
            ':measurementTypeId' => $measurementTypeId
        ]);

        return $lastMeasurement = $stmt->fetch();
    }
}