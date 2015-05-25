<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OAuthAccessToken
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\OAuthAccessTokenRepository")
 */
class OAuthAccessToken
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
     * @ORM\Column(name="token", type="string", length=100)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="string", length=100, nullable=true)
     */
    private $secret;

    /**
     * @var integer
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="foreign_user_id", type="string", length=255, nullable=true)
     */
    private $foreignUserId;

    /**
     * @var integer
     * @ORM\Column(name="service_provider_id", type="integer")
     */
    private $serviceProviderId;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="oauthAccessTokens")
     * @var User
     */
    protected $user;

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
     * Set token
     *
     * @param string $token
     *
     * @return OAuthAccessToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set secret
     *
     * @param string $secret
     *
     * @return OAuthAccessToken
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set foreignUserId
     *
     * @param string $foreignUserId
     *
     * @return OAuthAccessToken
     */
    public function setForeignUserId($foreignUserId)
    {
        $this->foreignUserId = $foreignUserId;

        return $this;
    }

    /**
     * Get foreignUserId
     *
     * @return string
     */
    public function getForeignUserId()
    {
        return $this->foreignUserId;
    }

    /**
     * Set serviceProviderId
     *
     * @param integer $serviceProviderId
     *
     * @return OAuthAccessToken
     */
    public function setServiceProviderId($serviceProviderId)
    {
        $this->serviceProviderId = $serviceProviderId;

        return $this;
    }

    /**
     * Get serviceProviderId
     *
     * @return integer
     */
    public function getServiceProviderId()
    {
        return $this->serviceProviderId;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

}

