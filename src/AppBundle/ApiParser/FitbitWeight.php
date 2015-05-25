<?php
namespace AppBundle\ApiParser;

use AppBundle\ApiParser\Fitbit\AbstractFitbitApiParser;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;

/**
 * Class FitbitWeight
 * @package AppBundle\ApiParser
 */
class FitbitWeight extends AbstractFitbitApiParser implements ApiParserInterface {

    /**
     * Create objects/arrays from an api response body
     *
     * @param $responseBody
     * @return mixed
     */
    public function parse($responseBody)
    {
        $this->parseError($responseBody);
        $json = json_decode($responseBody, true);

        $results = [
            'measurements' => [],
            'measurement_events' => []
        ];

        foreach ($json['weight'] as $weightMeasurement) {
            $measurementEvent = (new MeasurementEvent)
                ->setEventTime(new \DateTime($weightMeasurement['date'] . ' ' . $weightMeasurement['time']));

            $this->em->persist($measurementEvent);

            $results['measurement_events'][] = $measurementEvent;

            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::GRAMS])->getId();
            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::WEIGHT])->getId();
            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setUnits(($weightMeasurement['weight'] * 1000))
                ->setUnitsTypeId($unitTypeId)
                ->setMeasurementTypeId($measurementTypeId);

            $this->em->persist($measurement);

            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}