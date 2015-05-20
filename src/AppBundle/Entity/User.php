<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * Class User
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="MeasurementEvent", mappedBy="user")
     * @var MeasurementEvent[]
     **/
    private $measurementEvents;

    public function __construct()
    {
        parent::__construct();
        $this->measurementEvents = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getMeasurementEvents()
    {
        return $this->measurementEvents;
    }

    /**
     * @param mixed $measurementEvents
     */
    public function setMeasurementEvents($measurementEvents)
    {
        $this->measurementEvents = $measurementEvents;
    }
}