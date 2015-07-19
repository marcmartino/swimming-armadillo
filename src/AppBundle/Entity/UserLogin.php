<?php
namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 * @package AppBundle\Entity
 * @ORM\Entity
 */
class UserLogin {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var AdvancedUserInterface
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $time;

    public function __construct()
    {
        $this->time = new DateTime;
    }

    /**
     * @return AdvancedUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param AdvancedUserInterface $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
}