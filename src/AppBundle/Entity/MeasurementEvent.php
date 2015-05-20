<?php

namespace AppBundle\Entity;

use DateTime;
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
     * @var integer
     *
     * @ORM\Column(name="provider_id", type="integer")
     */
    private $providerId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="measurementEvents")
     * @var User
     */
    private $user;

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
     * Set providerId
     *
     * @param integer $providerId
     *
     * @return MeasurementEvent
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * Get providerId
     *
     * @return integer
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param DateTime $eventTime
     * @param $providerId
     * @return int - the id of the newly created measurement_event
     */
    public function store(
        DateTime $eventTime,
        $providerId
    ) {
        $this->setEventTime($eventTime)
            ->setProviderId($providerId);

        return $this;
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
}

