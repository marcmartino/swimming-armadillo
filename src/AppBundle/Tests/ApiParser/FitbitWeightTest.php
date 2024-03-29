<?php
namespace AppBundle\Tests\ApiParser;

use AppBundle\ApiParser\FitbitWeight;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;

use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * Class FitbitWeightTest
 * @package AppBundle\Tests\ApiParser
 */
class FitbitWeightTest extends AbstractApiParserTest {
    public function testParse()
    {
        $responseBody = file_get_contents(__DIR__ . '/../Resources/ApiParser/fitbitWeight.json');
        $parser = new FitbitWeight($this->getUnitTypes(), $this->getMeasurementTypes(), $this->getPersistence());
        $results = $parser->parse($responseBody);

        $this->assertCount(2, $results['measurements']);
        $this->assertCount(2, $results['measurement_events']);

        /** @var MeasurementEvent $measurementEvent */
        $measurementEvent = $results['measurement_events'][0];
        $expectedEventDateTime = new DateTime('2012-03-05 23:59:59');
        $this->assertEquals($expectedEventDateTime->getTimestamp(), $measurementEvent->getEventTime()->getTimestamp());

        /** @var Measurement $measurement */
        $measurement = $results['measurements'][1];
        $this->assertEquals(72500, $measurement->getUnits());
    }
}