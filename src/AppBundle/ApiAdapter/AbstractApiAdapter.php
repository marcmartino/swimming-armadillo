<?php
namespace AppBundle\ApiAdapter;

use AppBundle\Entity\User;
use AppBundle\Exception\UserNotAuthenticatedWithServiceProvider;
use DateTime;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\OAuthAccessToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractApiAdapter {

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var \OAuth\OAuth1\Service\AbstractService
     */
    protected $service;
    /** @var EntityManager */
    protected $em;

    /** @var User */
    protected $user;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param User $user
     */
    public function __construct(
        ContainerInterface $container,
        EntityManager $em,
        User $user
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Return a ServiceProvider object for this ApiAdapter
     * @return mixed
     */
    public abstract function getServiceProvider();

    /**
     * @return \OAuth\OAuth1\Service\AbstractService
     */
    public function getService()
    {
        return $this->service;
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
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}