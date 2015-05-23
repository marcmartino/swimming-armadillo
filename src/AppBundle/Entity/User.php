<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


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
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="RegistrationCode")
     * @ORM\JoinColumn(name="registrationcode_id", referencedColumnName="id")
     **/
    protected $registrationCode;

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Override since we use email as username
     * @param string $email
     * @return $this|void
     */
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);
    }

    /**
     * @return mixed
     */
    public function getRegistrationCode()
    {
        return $this->registrationCode;
    }

    /**
     * @param mixed $registrationCode
     */
    public function setRegistrationCode($registrationCode)
    {
        $this->registrationCode = $registrationCode;
    }
}