<?php
namespace AppBundle\ApiParser;

interface ApiParserInterface {
    /**
     * Create objects/arrays from an api response body
     *
     * @param $responseBody
     * @return mixed
     */
    public function parse($responseBody);
}