<?php
namespace AppBundle\ApiParser\Withings;

use AppBundle\ApiParser\AbstractEntityApiParser;
use AppBundle\ApiParser\ApiParserInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UnitType\UnitType;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class BodyMeasurement
 */
class BodyMeasurement extends AbstractEntityApiParser implements ApiParserInterface
{
    /**
     * Create objects/arrays from an api response body
     * Should return an array of measurements and measurement events like this:
     * ['measurements' => [], 'measurement_events' => []]
     *
     * @param $responseBody
     * @return mixed
     * @throws Exception
     */
    public function parse($responseBody)
    {
        $json = json_decode($responseBody, true);

        if ($json['status'] !== 0) {
            throw new \Exception("Request was unsuccessful.");
        }

        $results = [
            'measurements' => [],
            'measurement_events' => []
        ];

        foreach ($json['body']['measuregrps'] as $measureGroup) {

            $measurementEvent = new MeasurementEvent;

            $timezone = new DateTimeZone($json['body']['timezone']);
            $datetime = new DateTime(null, $timezone);
            $datetime->setTimestamp($measureGroup['date']);

            $measurementEvent->setEventTime($datetime);

            $this->persist->persist($measurementEvent);

            $measures = $measureGroup['measures'];

            foreach ($measures as $measurement) {

                $units = $measurement['value'];

                switch ($measurement['type']) {
                    case 1:  // weight
                        $measurementTypeSlug = MeasurementType::WEIGHT;
                        $unitsTypeSlug = UnitType::GRAMS;
                        break;
                    case 4:  // height
                        $measurementTypeSlug = MeasurementType::HEIGHT;
                        $unitsTypeSlug = UnitType::METERS;
                        break;
                    case 5:  // fat free mass
                        $measurementTypeSlug = MeasurementType::FAT_FREE_MASS;
                        $unitsTypeSlug = UnitType::GRAMS;
                        break;
                    case 6:  // fat ratio
                        $measurementTypeSlug = MeasurementType::FAT_RATIO;
                        $unitsTypeSlug = UnitType::PERCENT;
                        $units = $measurement['value'] * pow(10, $measurement['unit']);
                        break;
                    case 8:  // fat mass weight
                        $measurementTypeSlug = MeasurementType::FAT_MASS_WEIGHT;
                        $unitsTypeSlug = UnitType::GRAMS;
                        break;
                    case 11: // heart pulse
                        $measurementTypeSlug = MeasurementType::HEART_RATE;
                        $unitsTypeSlug = UnitType::BEATS_PER_MINUTE;
                        break;
                    default:
                        throw new Exception("Measurement type (" . $measurement['type'] . ") not handled");

                }

                $unitType = $this->unitTypes
                    ->findOneBy(['slug' => $unitsTypeSlug]);
                $measurementType = $this->measurementTypes
                    ->findOneBy(['slug' => $measurementTypeSlug]);

                $measurement = (new Measurement)
                    ->setMeasurementEvent($measurementEvent)
                    ->setUnitType($unitType)
                    ->setMeasurementType($measurementType)
                    ->setUnits($units);

                $this->persist->persist($measurement);

                $results['measurements'][] = $measurement;
            }
            $results['measurement_events'][] = $measurementEvent;
        }

        return $results;
    }
}