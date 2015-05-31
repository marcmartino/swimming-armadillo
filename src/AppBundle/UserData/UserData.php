<?php
namespace AppBundle\UserData;

use AppBundle\Entity\MeasurementRepository;

/**
 * TODO remove this class from existence and replace with entity repository class
 * Class UserData
 * @package AppBundle\UserData
 */
class UserData
{
    /** @var MeasurementRepository */
    protected $measurements;

    /**
     * @param MeasurementRepository $measurements
     */
    public function __construct(MeasurementRepository $measurements)
    {
        $this->measurements = $measurements;
    }

    /**
     * @param $measurementTypeId
     * @param $userId
     * @param \DateTime $startDatetime
     * @param \DateTime $endDatetime
     * @return array
     */
    public function getUserData($measurementTypeId, $userId, $startDatetime = null, $endDatetime = null)
    {
        return $this->measurements->getUserMeasurements($measurementTypeId, $userId, $startDatetime, $endDatetime);
    }
} 
