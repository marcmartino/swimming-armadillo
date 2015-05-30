<?php
namespace AppBundle\Tests\ApiParser;
use AppBundle\ApiParser\FitbitFood;
use AppBundle\Entity\Measurement;
use AppBundle\Persistence\EntityManagerPersistence;

/**
 * Class FitbitFoodTest
 * @package AppBundle\Tests\ApiParser
 */
class FitbitFoodTest extends AbstractApiParserTest
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

        $entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($unitTypeRepository));

        $persistence = new EntityManagerPersistence();

        $responseBody = file_get_contents(__DIR__ . '/../Resources/ApiParser/fitbitFood.json');
        $parser = new FitbitFood($unitTypeRepository, $measuremenTypeRepository, $persistence);
        $results = $parser->parse($responseBody);

        /** @var Measurement $calorieMeasurement */
        $calorieMeasurement = $results['measurements'][0];
        $this->assertEquals(752, $calorieMeasurement->getUnits());

        /** @var Measurement $carbsMeasurement */
        $carbsMeasurement = $results['measurements'][1];
        $this->assertEquals(66.5, $carbsMeasurement->getUnits());

        /** @var Measurement $fatMeasurement */
        $fatMeasurement = $results['measurements'][2];
        $this->assertEquals(49, $fatMeasurement->getUnits());

        /** @var Measurement $fiberMeasurement */
        $fiberMeasurement = $results['measurements'][3];
        $this->assertEquals(0.5, $fiberMeasurement->getUnits());

        /** @var Measurement $proteinMeasurement */
        $proteinMeasurement = $results['measurements'][4];
        $this->assertEquals(12.5, $proteinMeasurement->getUnits());

        /** @var Measurement $sodiumMeasurement */
        $sodiumMeasurement = $results['measurements'][5];
        $this->assertEquals(186, $sodiumMeasurement->getUnits());

        /** @var Measurement $waterMeasurement */
        $waterMeasurement = $results['measurements'][6];
        $this->assertEquals(0, $waterMeasurement->getUnits());
    }
} 