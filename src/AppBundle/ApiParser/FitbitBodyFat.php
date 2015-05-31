<?php
namespace AppBundle\ApiParser;

use AppBundle\ApiParser\Fitbit\AbstractFitbitApiParser;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;

/**
 * Class FitbitBodyFat
 * @package AppBundle\ApiParser
 */
class FitbitBodyFat extends AbstractFitbitApiParser implements ApiParserInterface {

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

        foreach ($json['fat'] as $fatMeasurement) {

            $measurementEvent = (new MeasurementEvent)
                ->setEventTime(new \DateTime($fatMeasurement['date'] . ' ' . $fatMeasurement['time']));
            $this->persist->persist($measurementEvent);

            $unitType = $this->unitTypes
                ->findOneBy(['slug' => UnitType::PERCENT]);
            $measurementType = $this->measurementTypes
                ->findOneBy(['slug' => MeasurementType::FAT_RATIO]);
            $measurement = (new Measurement)
                ->setMeasurementEvent($measurementEvent)
                ->setUnits($fatMeasurement['fat'])
                ->setUnitType($unitType)
                ->setMeasurementType($measurementType);
            $this->persist->persist($measurement);

            $results['measurement_events'][] = $measurementEvent;
            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}