<?php
namespace AppBundle\ApiParser;

use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;
use Doctrine\ORM\EntityManager;

class FitbitWeight implements ApiParserInterface {

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

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

        foreach ($json['weight'] as $weightMeasurement) {
            $measurementEvent = (new MeasurementEvent)
                ->setEventTime(new \DateTime($weightMeasurement['date'] . ' ' . $weightMeasurement['time']));

            $this->em->persist($measurementEvent);

            $results['measurement_events'][] = $measurementEvent;

            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setUnits(($weightMeasurement['weight'] * 1000));

            $this->em->persist($measurement);

            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}