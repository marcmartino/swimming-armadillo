<?php
namespace AppBundle\ApiParser;

use AppBundle\ApiParser\Fitbit\AbstractFitbitApiParser;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementTypeRepository;
use AppBundle\Entity\UnitTypeRepository;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\UnitType\UnitType;

/**
 * Class FitbitFood
 * @package AppBundle\ApiParser
 */
class FitbitFood extends AbstractFitbitApiParser implements ApiParserInterface
{
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

        $measurementEvent = new MeasurementEvent;
        $this->persist->persist($measurementEvent);

        $calorieMeasurement = $this->getSummaryMeasurement(UnitType::CALORIES, MeasurementType::DAILY_CALORIES,
            $json['summary']['calories']);

        $carbMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_CARBS,
            $json['summary']['carbs']);

        $fatMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_FAT,
            $json['summary']['fat']);

        $fiberMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_FIBER,
            $json['summary']['fiber']);

        $proteinMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_PROTEIN,
            $json['summary']['protein']);

        $sodiumMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_SODIUM,
            $json['summary']['sodium']);

        $waterMeasurement = $this->getSummaryMeasurement(UnitType::GRAMS, MeasurementType::DAILY_WATER,
            $json['summary']['water']);

        $results['measurement_events'][] = $measurementEvent;
        $results['measurements'] = [$calorieMeasurement, $carbMeasurement, $fatMeasurement, $fiberMeasurement,
            $proteinMeasurement, $sodiumMeasurement, $waterMeasurement];

        /** @var Measurement $measurement */
        foreach ($results['measurements'] as $measurement) {
            $measurement->setMeasurementEvent($measurementEvent);
            $this->persist->persist($measurement);
        }

        return $results;
    }
}