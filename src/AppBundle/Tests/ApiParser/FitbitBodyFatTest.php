<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 4/26/15
 * Time: 6:07 PM
 */

namespace AppBundle\Tests\ApiParser;


use AppBundle\ApiParser\FitbitBodyFat;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use DateTime;

class FitbitBodyFatTest extends \PHPUnit_Framework_TestCase{
    public function testParse()
    {
        $entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $responseBody = file_get_contents(__DIR__ . '/../Resources/ApiParser/fitbitBodyFat.json');
        $parser = new FitbitBodyFat($entityManager);
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