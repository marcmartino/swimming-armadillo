<?php
namespace AppBundle\ApiParser;

use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;

/**
 * Class FitbitBodyFat
 * @package AppBundle\ApiParser
 */
class FitbitBodyFat extends AbstractEntityApiParser implements ApiParserInterface {

    /**
     * Create objects/arrays from an api response body
     *
     * @param $responseBody
     * @return mixed
     */
    public function parse($responseBody)
    {
        $json = json_decode($responseBody, true);

        $results = [
            'measurements' => [],
            'measurement_events' => []
        ];

        foreach ($json['fat'] as $fatMeasurement) {

            $measurementEvent = (new MeasurementEvent)
                ->setEventTime(new \DateTime($fatMeasurement['date'] . ' ' . $fatMeasurement['time']));
            $this->em->persist($measurementEvent);

            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::PERCENT])->getId();
            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::FAT_RATIO])->getId();
            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setUnits($fatMeasurement['fat'])
                ->setUnitsTypeId($unitTypeId)
                ->setMeasurementTypeId($measurementTypeId);
            $this->em->persist($measurement);

            $results['measurement_events'][] = $measurementEvent;
            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}