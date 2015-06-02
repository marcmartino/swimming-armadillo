<?php
namespace AppBundle\ApiParser\Automatic;
use AppBundle\ApiParser\AbstractEntityApiParser;
use AppBundle\ApiParser\ApiParserInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;

/**
 * Class Trips
 * @package AppBundle\Automatic\Trips
 */
class Trips extends AbstractEntityApiParser implements ApiParserInterface
{
    /**
     * Create objects/arrays from an api response body
     * Should return an array of measurements and measurement events like this:
     * ['measurements' => [], 'measurement_events' => []]
     *
     * @param $responseBody
     * @return mixed
     */
    public function parse($responseBody)
    {
        $return = [
            'measurements' => [],
            'measurement_events' => []
        ];
        $trips = $this->consumeTrips($responseBody);

        foreach ($trips['events'] as $measurementEvent) {
            $measurementEventObj = new MeasurementEvent();
            $eventTime = new \DateTime($measurementEvent['event_time']);
            $measurementEventObj->setEventTime($eventTime);
            $this->persist->persist($measurementEventObj);
            $return['measurement_events'][] = $measurementEventObj;

            $measurement = $measurementEvent['measurements'];
            // Store drive distance
            $distance = $measurement['distance'];
            $measurementObj = new Measurement();
            $unitType = $this->unitTypes
                ->findOneBy(['slug' => UnitType::METERS]);
            $measurementType = $this->measurementTypes
                ->findOneBy(['slug' => MeasurementType::DRIVE_DISTANCE]);
            $measurementObj->setMeasurementEvent($measurementEventObj)
                ->setMeasurementType($measurementType)
                ->setUnitType($unitType)
                ->setUnits($distance);
            $this->persist->persist($measurementObj);
            $return['measurements'][] = $measurementObj;

            // Store drive time
            $driveTime = $measurement['drive_time'];
            $measurementObj = new Measurement();
            $unitType = $this->unitTypes
                ->findOneBy(['slug' => UnitType::SECONDS]);
            $measurementType = $this->measurementTypes
                ->findOneBy(['slug' => MeasurementType::DRIVE_TIME]);
            $measurementObj->setMeasurementEvent($measurementEventObj)
                ->setMeasurementType($measurementType)
                ->setUnitType($unitType)
                ->setUnits($driveTime);
            $this->persist->persist($measurementObj);
            $return['measurements'][] = $measurementObj;
        }
        return $return;
    }

    /**
     * @param $responseBody
     * @return array
     */
    public function consumeTrips($responseBody)
    {
        $tripEvents = ['events' => []];
        $json = json_decode($responseBody, true);
        foreach ($json as $event) {
            $driveTime = ($event['end_time'] - $event['start_time']) / 1000;
            $tripEvent = [
                'event_time' => date('Y-m-d H:i:s', $event['start_time'] / 1000),
                'measurements' => ['distance' => $event['distance_m'], 'drive_time' => $driveTime]
            ];
            $tripEvents['events'][] = $tripEvent;
        }

        return $tripEvents;
    }
}