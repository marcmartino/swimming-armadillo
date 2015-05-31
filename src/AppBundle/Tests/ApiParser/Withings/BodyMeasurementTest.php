<?php
namespace AppBundle\Tests\ApiParser\Withings;

use AppBundle\ApiParser\Withings\BodyMeasurement;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Tests\ApiParser\AbstractApiParserTest;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class BodyMeasurementTest
 */
class BodyMeasurementTest extends AbstractApiParserTest
{
    public function testParse()
    {
        $unitType = $this->getMock('\AppBundle\Entity\UnitType');
        $unitType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1000));

        $measurementType = $this->getMock('\AppBundle\Entity\UnitType');
        $measurementType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(50));

        $unitTypeRepository = $this->getUnitTypes();
        $unitTypeRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($unitType));

        $measuremenTypeRepository = $this->getMeasurementTypes();
        $measuremenTypeRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($measurementType));

        $responseBody = file_get_contents(__DIR__ . '/../../Resources/ApiParser/Withings/bodymeasures.json');
        $parser = new BodyMeasurement($unitTypeRepository, $measuremenTypeRepository, $this->getPersistence());
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
        $responseBody = file_get_contents(__DIR__ . '/../../Resources/ApiParser/Withings/bodymeasures247.json');
        $parser = new BodyMeasurement($this->getUnitTypes(), $this->getMeasurementTypes(), $this->getPersistence());
        $parser->parse($responseBody);
    }
} 