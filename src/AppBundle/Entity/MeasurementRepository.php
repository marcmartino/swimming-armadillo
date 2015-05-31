<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class MeasurementRepository
 * @package AppBundle\Entity
 */
class MeasurementRepository extends EntityRepository
{
    /**
     * @param $measurementTypeId
     * @param $userId
     * @param null $startDatetime
     * @param null $endDatetime
     * @return mixed
     */
    public function getUserMeasurements($measurementTypeId, $userId, $startDatetime = null, $endDatetime = null)
    {
        $query = "SELECT me.event_time, m.units, m.unit_type_id
            FROM measurementevent me INNER JOIN measurement m
            ON me.id = m.measurement_event_id
            WHERE m.measurement_type_id = :measurementType
            AND me.user_id = :userId";

        $parameters = [
            ':measurementType' => $measurementTypeId,
            ':userId' => $userId
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

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }
}