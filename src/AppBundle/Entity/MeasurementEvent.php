<?php
namespace AppBundle\Entity;

use DateTime;

/**
 * Class MeasurementEvent
 * @package AppBundle\Entity
 */
class MeasurementEvent extends AbstractEntity
{
    /**
     * @param DateTime $eventTime
     * @param $providerId
     * @return int - the id of the newly created measurement_event
     */
    public function store(
        DateTime $eventTime,
        $providerId
    ) {
        $eventQuery = 'INSERT INTO measurement_event (event_time, provider_id) VALUES (:event_time, :provider_id)';
        $eventStmt = $this->conn->prepare($eventQuery);
        $eventStmt->execute([
            ':event_time' => $eventTime->format('Y-m-d H:i:s'),
            ':provider_id' => $providerId
        ]);

        return $this->conn->lastInsertId('measurement_event_id_seq');
    }
}