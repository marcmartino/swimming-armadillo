<?php
namespace AppBundle\Tests\ApiParser;

use AppBundle\ApiParser\FitbitBodyFat;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use DateTime;

/**
 * Class FitbitBodyFatTest
 * @package AppBundle\Tests\ApiParser
 */
class FitbitBodyFatTest extends AbstractApiParserTest {
    public function testParse()
    {
        $responseBody = file_get_contents(__DIR__ . '/../Resources/ApiParser/fitbitBodyFat.json');
        $parser = new FitbitBodyFat($this->getUnitTypes(), $this->getMeasurementTypes(), $this->getPersistence());
        $results = $parser->parse($responseBody);
        
        $this->assertCount(2, $results['measurements']);
        $this->assertCount(2, $results['measurement_events']);

        /** @var MeasurementEvent $measurementEvent */
        $measurementEvent = $results['measurement_events'][0];
        $expectedEventDateTime = new DateTime('2012-03-05 23:59:59');
        $this->assertEquals($expectedEventDateTime->getTimestamp(), $measurementEvent->getEventTime()->getTimestamp());

        /** @var Measurement $measurement */
        $measurement = $results['measurements'][1];
        $this->assertEquals(13.5, $measurement->getUnits());
    }
}