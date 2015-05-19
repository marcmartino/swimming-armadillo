<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiParser\FitbitBodyFat;
use AppBundle\ApiParser\FitbitFood;
use AppBundle\ApiParser\FitbitWeight;
use AppBundle\Entity\User;
use DateTime;
use OAuth\ServiceFactory;
use Doctrine\ORM\EntityManager;
use OAuth\OAuth1\Service\FitBit;
use AppBundle\Provider\Providers;
use AppBundle\Entity\Measurement;
use OAuth\Common\Storage\Session;
use AppBundle\Entity\ServiceProvider;
use OAuth\Common\Consumer\Credentials;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FitbitApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class FitbitApiAdapter extends AbstractOAuthApiAdapter implements ApiAdapterInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var FitBit */
    protected $service;
    /** @var Session */
    protected $storage;
    /** @var EntityManager */
    protected $em;
    /** @var FitbitFood */
    protected $fitbitFood;
    /** @var FitbitBodyFat  */
    protected $fitbitBodyFat;
    /** @var FitbitWeight */
    protected $fitbitWeight;

    public function __construct(
        ContainerInterface $container,
        EntityManager $em,
        User $user
    )
    {
        parent::__construct($container, $em, $user);
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');

        $this->service = $this->createService();
        $this->em = $em;

        $this->fitbitFood = $this->container->get('api_parser.fitbit_food');
        $this->fitbitBodyFat = $this->container->get('api_parser.fitbit_bodyfat');
        $this->fitbitWeight = $this->container->get('api_parser.fitbit_weight');
    }

    /**
     * @return \OAuth\Common\Service\ServiceInterface
     * @throws \OAuth\Common\Exception\Exception
     */
    protected function createService()
    {
        $credentials = new Credentials(
            $this->container->getParameter('fitbit_consumer_key'),
            $this->container->getParameter('fitbit_consumer_secret'),
            $this->container->getParameter('fitbit_callback_uri')
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('FitBit', 'AppBundle\\OAuth\\FitBit');

        /** @var $fitbitService FitBit */
        return $fitbitService = $serviceFactory->createService('FitBit', $credentials, $this->storage);
    }

    public function consumeData()
    {
        // Ensure the user has authenticated with fitbit
        $userOauthToken = $this->getUserOauthToken();

        // Consume data for the last day (should be changed)
        $date = new \DateTime;
        $date = new \DateTime($date->format('Y-m-d'));
        $date->modify('-1 week');

        // We have to fetch food results with one request per day
        $this->consumeFood($date, (new DateTime));

        // Fetch a months worth of body fat
        $from = (new DateTime)->modify('-1 month');
        $to = (new DateTime);
        $this->consumeBodyFat($from, $to);

        // Fetch a months worth of weight
        $from = (new DateTime)->modify('-1 month');
        $to = (new DateTime);
        $this->consumeWeight($from, $to);

        $this->em->flush();
    }

    public function consumeFood(DateTime $dateFrom, DateTime $dateTo)
    {
        while ($dateFrom <= $dateTo) {

            $uri = '/user/-/foods/log/date/' . $dateFrom->format('Y-m-d') . '.json';
            $response = $this->getService()->request($uri);

            $fitbitResults = $this->fitbitFood->parse($response);

            /** @var MeasurementEvent $measurementEvent */
            foreach ($fitbitResults['measurement_events'] as $measurementEvent) {
                $measurementEvent->setEventTime($dateFrom);
                $measurementEvent->setProviderId($this->getServiceProvider()->getId());
                $measurementEvent->setUser($this->getUser());
                $this->em->persist($measurementEvent);
            }

            $dateFrom->modify('+1 day');
        }
    }

    public function consumeBodyFat(DateTime $dateFrom, DateTime $dateTo)
    {
        $uri = '/user/-/body/log/fat/date/' . $dateFrom->format('Y-m-d') . '/' . $dateTo->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);
        $bodyfatResults = $this->fitbitBodyFat->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($bodyfatResults['measurement_events'] as $measurementEvent) {
            $measurementEvent->setProviderId($this->getServiceProvider()->getId());
            $measurementEvent->setUser($this->getUser());
            $this->em->persist($measurementEvent);
        }
    }

    public function consumeWeight(Datetime $dateFrom, DateTime $dateTo)
    {
        $uri = '/user/-/body/log/weight/date/' . $dateFrom->format('Y-m-d') . '/' . $dateTo->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);

        $weightResults = $this->fitbitWeight->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($weightResults['measurement_events'] as $measurementEvent) {
            $measurementEvent->setProviderId($this->getServiceProvider()->getId());
            $measurementEvent->setUser($this->getUser());
            $this->em->persist($measurementEvent);
        }
    }

    public function handleCallback()
    {
        $token = $this->storage->retrieveAccessToken('FitBit');

        $accessToken = $this->getService()->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $token->getRequestTokenSecret()
        );

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUserId($securityContext->getToken()->getUser()->getId())
            ->setServiceProviderId($this->getServiceProvider()->getId())
            ->setToken($accessToken->getAccessToken())
            ->setSecret($accessToken->getAccessTokenSecret());

        $this->em->persist($accessTokenObj);
        $this->em->flush();
    }

    /**
     * Return a service provider entity for fitbit
     *
     * @return null|ServiceProvider
     */
    public function getServiceProvider()
    {
        return $provider = $this->em->getRepository('AppBundle:ServiceProvider')
            ->findOneBy(['slug' => Providers::FITBIT]);
    }
}