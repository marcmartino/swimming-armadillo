<?php
namespace AppBundle\ApiAdapter;

use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

abstract class AbstractApiAdapter {

    /**
     * @var Container
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
     * @param Container $container
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
     * @return null|OauthAccessToken
     * @throws \Exception
     */
    public function getUserOauthToken()
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser()->getId();

        $oauthToken = $this->em->getRepository('AppBundle:OAuthAccessToken')
            ->findOneBy([
                'userId' => $user,
                'serviceProviderId' => $this->getServiceProvider()->getId()
            ]);

        if (empty($oauthToken)) {
            throw new \Exception("User has not authenticated service provider: " . $this->getServiceProvider()->getSlug());
        }

        return $oauthToken;
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