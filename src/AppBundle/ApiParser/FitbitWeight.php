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

            $this->persist->persist($measurementEvent);

            $results['measurement_events'][] = $measurementEvent;

            $unitType = $this->unitTypes
                ->findOneBy(['slug' => UnitType::GRAMS]);
            $measurementType = $this->measurementTypes
                ->findOneBy(['slug' => MeasurementType::WEIGHT]);
            $measurement = (new Measurement)
                ->setMeasurementEvent($measurementEvent)
                ->setUnits(($weightMeasurement['weight'] * 1000))
                ->setUnitType($unitType)
                ->setMeasurementType($measurementType);

            $this->persist->persist($measurement);

            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}