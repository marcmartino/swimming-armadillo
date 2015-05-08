<?php
namespace AppBundle\ApiParser;

interface ApiParserInterface {
    /**
     * Create objects/arrays from an api response body
     * Should return an array of measurements and measurement events like this:
     * ['measurements' => [], 'measurement_events' => []]
     *
     * @param $responseBody
     * @return mixed
     */
    public function parse($responseBody);
}