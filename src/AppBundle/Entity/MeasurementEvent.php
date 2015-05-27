<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementEvent
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MeasurementEvent
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
     * @var \DateTime
     *
     * @ORM\Column(name="event_time", type="datetimetz")
     */
    private $eventTime;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="measurementEvents")
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Measurement", mappedBy="measurementEvent")
     * @var Measurement[]
     **/
    private $measurements;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceProvider")
     * @var ServiceProvider
     */
    private $serviceProvider;

    public function __construct()
    {
        $this->measurements = new ArrayCollection();
    }

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
     * Set eventTime
     *
     * @param \DateTime $eventTime
     *
     * @return MeasurementEvent
     */
    public function setEventTime($eventTime)
    {
        $this->eventTime = $eventTime;

        return $this;
    }

    /**
     * Get eventTime
     *
     * @return \DateTime
     */
    public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Measurement[]
     */
    public function getMeasurements()
    {
        return $this->measurements->toArray();
    }

    /**
     * @param Measurement $measurement
     * @return $this
     */
    public function addMeasurement(Measurement $measurement)
    {
        if (!$this->measurements->contains($measurement)) {
            $this->measurements->add($measurement);
        }

        return $this;
    }

    /**
     * @return ServiceProvider
     */
    public function getServiceProvider()
    {
        return $this->serviceProvider;
    }

    /**
     * @param ServiceProvider $serviceProvider
     */
    public function setServiceProvider($serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }
}

