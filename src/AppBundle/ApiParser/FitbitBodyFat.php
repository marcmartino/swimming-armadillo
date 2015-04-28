<?php
namespace AppBundle\ApiParser;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;

class FitbitBodyFat implements ApiParserInterface {

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

        foreach ($json['fat'] as $fatMeasurement) {

            $measurementEvent = (new MeasurementEvent)
                ->setEventTime(new \DateTime($fatMeasurement['date'] . ' ' . $fatMeasurement['time']));
            $this->em->persist($measurementEvent);

            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setUnits($fatMeasurement['fat']);
            $this->em->persist($measurement);

            $results['measurement_events'][] = $measurementEvent;
            $results['measurements'][] = $measurement;
        }

        return $results;
    }
}