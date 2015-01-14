<?php
namespace AppBundle\UserData;

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
     * @param \Doctrine\DBAL\Connection|\PDO $conn
     */
    public function __construct(\Doctrine\DBAL\Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        $measurementEventArrays = [];

        $stmt = $this->conn->prepare("
        SELECT me.id, me.event_time
        FROM measurement_event me INNER JOIN measurement m
        ON me.id = m.measurement_event_id
        WHERE m.measurement_type_id = 2 OR m.measurement_type_id = 4 OR m.measurement_type_id = 6
        ");
        $stmt->execute();

        $measurementEvents = $stmt->fetchAll();

        $stmt = $this->conn->prepare("
            SELECT measurement_type_id, units_type_id, units
            FROM measurement
            WHERE measurement_event_id = :measurementEventId
        ");

        foreach ($measurementEvents as $measurementEvent) {

            $measurementEventArray = ['time' => $measurementEvent['event_time'], 'measurements' => []];

            $stmt->execute([':measurementEventId' => $measurementEvent['id']]);

            foreach ($stmt->fetchAll() as $measurement) {
                $measurementEventArray['measurements'][] =[
                    'units' => $measurement['units'],
                    'type' => $measurement['measurement_type_id'],
                    'unit_type' => $measurement['units_type_id']
                ];
            }

            $measurementEventArrays[] = $measurementEventArray;
        }

        return $measurementEventArrays;
    }
} 
