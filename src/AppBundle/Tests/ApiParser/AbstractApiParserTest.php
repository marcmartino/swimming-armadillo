<?php
namespace AppBundle\Tests\ApiParser;

use AppBundle\Persistence\EntityManagerPersistence;

/**
 * Class AbstractApiParserTest
 * @package AppBundle\Tests\ApiParser
 */
abstract class AbstractApiParserTest extends \PHPUnit_Framework_TestCase
{
    public function getUnitTypes()
    {
        return $this
            ->getMockBuilder('\AppBundle\Entity\UnitTypeRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getMeasurementTypes()
    {
        return $this
            ->getMockBuilder('\AppBundle\Entity\MeasurementTypeRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getPersistence()
    {
        return new EntityManagerPersistence();
    }
}