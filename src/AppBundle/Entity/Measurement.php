<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Measurement
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * @var integer
     *
     * @ORM\Column(name="measurement_event_id", type="integer")
     */
    private $measurementEventId;

    /**
     * @var integer
     *
     * @ORM\Column(name="measurement_type_id", type="integer")
     */
    private $measurementTypeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="units_type_id", type="integer")
     */
    private $unitsTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="units", type="decimal")
     */
    private $units;


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
     * Set measurementEventId
     *
     * @param integer $measurementEventId
     *
     * @return Measurement
     */
    public function setMeasurementEventId($measurementEventId)
    {
        $this->measurementEventId = $measurementEventId;

        return $this;
    }

    /**
     * Get measurementEventId
     *
     * @return integer
     */
    public function getMeasurementEventId()
    {
        return $this->measurementEventId;
    }

    /**
     * Set measurementTypeId
     *
     * @param integer $measurementTypeId
     *
     * @return Measurement
     */
    public function setMeasurementTypeId($measurementTypeId)
    {
        $this->measurementTypeId = $measurementTypeId;

        return $this;
    }

    /**
     * Get measurementTypeId
     *
     * @return integer
     */
    public function getMeasurementTypeId()
    {
        return $this->measurementTypeId;
    }

    /**
     * Set unitsTypeId
     *
     * @param integer $unitsTypeId
     *
     * @return Measurement
     */
    public function setUnitsTypeId($unitsTypeId)
    {
        $this->unitsTypeId = $unitsTypeId;

        return $this;
    }

    /**
     * Get unitsTypeId
     *
     * @return integer
     */
    public function getUnitsTypeId()
    {
        return $this->unitsTypeId;
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
     * @param $measurementEventId
     * @param $measurementTypeId
     * @param $unitsTypeId
     * @param $units
     * @return Measurement
     */
    public function store(
        $measurementEventId,
        $measurementTypeId,
        $unitsTypeId,
        $units
    ) {
        $measurement = (new Measurement)
            ->setMeasurementEventId($measurementEventId)
            ->setMeasurementTypeId($measurementTypeId)
            ->setUnitsTypeId($unitsTypeId)
            ->setUnits($units);
        return $measurement;
    }
}

