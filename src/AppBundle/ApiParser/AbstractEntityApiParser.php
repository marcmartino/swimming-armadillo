<?php
namespace AppBundle\ApiParser;

use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementTypeRepository;
use AppBundle\Entity\UnitTypeRepository;
use AppBundle\Persistence\PersistenceInterface;

/**
 * Subclasses should create Doctrine Entities with information they have from api responses
 *
 * Class AbstractEntityApiParser
 * @package AppBundle\ApiParser
 */
class AbstractEntityApiParser
{
    /**
     * @var UnitTypeRepository
     */
    protected $unitTypes;
    /**
     * @var MeasurementTypeRepository
     */
    protected $measurementTypes;
    /**
     * @var PersistenceInterface
     */
    protected $persist;

    /**
     * @param UnitTypeRepository $unitTypes
     * @param MeasurementTypeRepository $measurementTypes
     * @param PersistenceInterface $persist
     */
    public function __construct(
        UnitTypeRepository $unitTypes,
        MeasurementTypeRepository $measurementTypes,
        PersistenceInterface $persist
    )
    {
        $this->unitTypes = $unitTypes;
        $this->measurementTypes = $measurementTypes;
        $this->persist = $persist;
    }

    /**
     * @param $unitTypeSlug
     * @param $measurementTypeSlug
     * @param $units
     * @return Measurement
     */
    protected function getSummaryMeasurement($unitTypeSlug, $measurementTypeSlug, $units)
    {
        $unitType = $this->unitTypes
            ->findOneBy(['slug' => $unitTypeSlug]);
        $measurementType = $this->measurementTypes
            ->findOneBy(['slug' => $measurementTypeSlug]);
        $measurement = (new Measurement)
            ->setUnits($units)
            ->setUnitType($unitType)
            ->setMeasurementType($measurementType);

        return $measurement;
    }
}