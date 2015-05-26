<?php
namespace AppBundle\ApiAdapter;

use AppBundle\Entity\User;
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
     * Get the date and time to start consuming a service provider's api
     *
     * @return DateTime
     */
    public function getStartConsumeDateTime()
    {
        if($dateTime = $this->getLastMeasurementEventDateTime()) {
            return $dateTime;
        }
        return $this->getDefaultConsumeDateTime();
    }

    /**
     * Get date and time of the most recent data we have for this service provider
     *
     * @return bool|\DateTime
     */
    public function getLastMeasurementEventDateTime()
    {
        $measurementEventRepo = $this->em->getRepository('AppBundle:MeasurementEvent');
        /** @var \AppBundle\Entity\MeasurementEvent|bool $lastMeasurementEvent */
        $lastMeasurementEvent = $measurementEventRepo->findOneBy(
            ['user' => $this->getUser(), 'providerId' => $this->getServiceProvider()->getId()],
            ['eventTime' => 'DESC']
        );
        if (empty($lastMeasurementEvent)) {
            return false;
        }
        return $lastMeasurementEvent->getEventTime();
    }

    /**
     * Returns default start time for consuming provider apis, override if necessary
     *
     * @return DateTime
     */
    public function getDefaultConsumeDateTime()
    {
        return (new DateTime)->modify('-1 month');
    }

    /**
     * @return null|OauthAccessToken
     * @throws \Exception
     */
    public function getUserOauthToken()
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();

        $oauthToken = $this->em->getRepository('AppBundle:OAuthAccessToken')
            ->findOneBy([
                'user' => $user,
                'serviceProvider' => $this->getServiceProvider()
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