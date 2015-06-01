<?php
namespace AppBundle\Tests\ApiParser;

use AppBundle\Persistence\PersistenceInterface;
use Mockery;

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

    /**
     * @return Mockery\MockInterface
     */
    public function getPersistence()
    {
        return Mockery::mock('AppBundle\Persistence\PersistenceInterface', ['persist' => true]);
    }
}