<?php
namespace AppBundle\Tests\ApiParser\Withings;

use AppBundle\ApiParser\Withings\BodyMeasurement;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class BodyMeasurementTest
 */
class BodyMeasurementTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $this->markTestIncomplete('Need to fix');
        $unitType = $this->getMock('\AppBundle\Entity\UnitType');
        $unitType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1000));

        $measurementType = $this->getMock('\AppBundle\Entity\UnitType');
        $measurementType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(50));

        $unitTypeRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $unitTypeRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($unitType));

        $measuremenTypeRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $measuremenTypeRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($measurementType));

        $entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($unitTypeRepository));

        $responseBody = file_get_contents(__DIR__ . '/../../Resources/ApiParser/Withings/bodymeasures.json');
        $parser = new BodyMeasurement($entityManager);
        $results = $parser->parse($responseBody);

        $this->assertCount(5, $results['measurements']);
        $this->assertCount(2, $results['measurement_events']);

        /** @var MeasurementEvent $measurementEvent */
        $measurementEvent = $results['measurement_events'][0];
        $timezone = new DateTimeZone('Europe/Paris');
        $expectedEventDateTime = new DateTime('2008-10-02 9:02:48', $timezone);
        $this->assertEquals($expectedEventDateTime->format('c'), $measurementEvent->getEventTime()->format('c'));

        /** @var Measurement $measurement */
        $measurement = $results['measurements'][0];
        $this->assertEquals(79300, $measurement->getUnits());
    }

    /**
     * @expectedException Exception
     */
    public function testParse247()
    {
        $this->markTestIncomplete('Need to fix');
        $entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $responseBody = file_get_contents(__DIR__ . '/../../Resources/ApiParser/Withings/bodymeasures247.json');
        $parser = new BodyMeasurement($entityManager);
        $parser->parse($responseBody);
    }
} 