<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Measurement
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MeasurementRepository")
 */
class Measurement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="units", type="decimal")
     */
    private $units;

    /**
     * @var MeasurementEvent
     * @ORM\ManyToOne(targetEntity="MeasurementEvent", inversedBy="measurements")
     * @ORM\JoinColumn(name="measurement_event_id", referencedColumnName="id")
     */
    private $measurementEvent;

    /**
     * (e.g. weight, height, daily calories, drive distance)
     * @var MeasurementType
     * @ORM\ManyToOne(targetEntity="MeasurementType")
     * @ORM\JoinColumn(name="measurement_type_id", referencedColumnName="id")
     */
    private $measurementType;

    /**
     * (e.g. kilograms, meters, calories, kilometers)
     * @var UnitType
     * @ORM\ManyToOne(targetEntity="UnitType")
     * @ORM\JoinColumn(name="unit_type_id", referencedColumnName="id")
     */
    private $unitType;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set units
     *
     * @param string $units
     *
     * @return Measurement
     */
    public function setUnits($units)
    {
        $this->units = $units;

        return $this;
    }

    /**
     * Get units
     *
     * @return string
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @return MeasurementEvent
     */
    public function getMeasurementEvent()
    {
        return $this->measurementEvent;
    }

    /**
     * @param MeasurementEvent $measurementEvent
     * @return $this
     */
    public function setMeasurementEvent($measurementEvent)
    {
        $this->measurementEvent = $measurementEvent;

        return $this;
    }

    /**
     * @return MeasurementType
     */
    public function getMeasurementType()
    {
        return $this->measurementType;
    }

    /**
     * @param MeasurementType $measurementType
     * @return $this
     */
    public function setMeasurementType($measurementType)
    {
        $this->measurementType = $measurementType;

        return $this;
    }

    /**
     * @return UnitType
     */
    public function getUnitType()
    {
        return $this->unitType;
    }

    /**
     * @param UnitType $unitType
     * @return $this
     */
    public function setUnitType($unitType)
    {
        $this->unitType = $unitType;

        return $this;
    }
}

