<?php
namespace AppBundle\ApiParser\Fitbit;

use AppBundle\ApiParser\AbstractEntityApiParser;
use AppBundle\Exception\ApiParserException;


/**
 * Class AbstractFitbitApiParser
 */
class AbstractFitbitApiParser extends AbstractEntityApiParser
{
    /**
     * Check response and parse any errors
     */
    public function parseError($responseBody)
    {
        $json = json_decode($responseBody, true);
        if ($json['success'] == false) {
            throw new ApiParserException('Fibit api request failed: ' . $responseBody);
        }
    }
}