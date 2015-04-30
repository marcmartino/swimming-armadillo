<?php
use AppBundle\ApiParser\AbstractEntityApiParser;
use AppBundle\ApiParser\ApiParserInterface;

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
     */
    public function parse($responseBody)
    {
        // TODO: Implement parse() method.
    }
}