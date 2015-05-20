<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementType as MeasurementTypeService;
use AppBundle\Entity\UnitType as UnitTypeService;
use AppBundle\Entity\User;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
use Doctrine\ORM\EntityManager;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\ServiceFactory;
use AppBundle\OAuth\AutomaticOAuth2;
use OAuth\Common\Consumer\Credentials;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AutomaticApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class AutomaticApiAdapter extends AbstractOAuthApiAdapter implements ApiAdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var AutomaticOAuth2
     */
    protected $service;
    /**
     * @var UnitTypeService
     */
    protected $unitTypeService;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param User $user
     */
    public function __construct(ContainerInterface $container, EntityManager $em, User $user)
    {
        parent::__construct($container, $em, $user);
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');

        $this->consumerKey = $this->container->getParameter('automatic_consumer_key');
        $this->consumerSecret = $this->container->getParameter('automatic_consumer_secret');
        $this->callbackUri = $this->container->getParameter('automatic_callback_uri');

        $this->service = $this->createService();
        $this->unitTypeService = $this->container->get('entity_unit_type');
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        return $this->getService()->getAuthorizationUri();
    }

    /**
     * @return mixed
     */
    public function consumeData()
    {
        /** @var MeasurementEvent $measurementEventService */
        $measurementEventService = $this->container->get('entity.measurement_event');
        /** @var Measurement $measurementService */
        $measurementService = $this->container->get('entity.measurement');
        /** @var MeasurementTypeService $measurementTypeService */
        $measurementTypeService = $this->container->get('entity_measurement_type');

        /** @var Provider $provider */
        $provider = $this->container->get('entity_provider');

        $response = $this->getService()->request('/trips');

        $trips = $this->consumeTrips($response);

        foreach ($trips['events'] as $measurementEvent) {
            $measurementEventObj = new MeasurementEvent();
            $eventTime = new \DateTime($measurementEvent['event_time']);
            $measurementEventObj->setEventTime($eventTime)
                ->setProviderId($this->getServiceProvider()->getId())
                ->setUser($this->getUser());
            $this->em->persist($measurementEventObj);

            $measurement = $measurementEvent['measurements'];
            // Store drive distance
            $distance = $measurement['distance'];
            $measurementObj = new Measurement();
            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::METERS])->getId();
            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::DRIVE_DISTANCE])->getId();
            $measurementObj->setMeasurementEventId($measurementEventObj->getId())
                ->setMeasurementTypeId($measurementTypeId)
                ->setUnitsTypeId($unitTypeId)
                ->setUnits($distance);
            $this->em->persist($measurementObj);

            // Store drive time
            $driveTime = $measurement['drive_time'];
            $measurementObj = new Measurement();
            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::SECONDS])->getId();
            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::DRIVE_TIME])->getId();
            $measurementObj->setMeasurementEventId($measurementEventObj->getId())
                ->setMeasurementTypeId($measurementTypeId)
                ->setUnitsTypeId($unitTypeId)
                ->setUnits($driveTime);
            $this->em->persist($measurementObj);
        }
        $this->em->flush();
    }

    public function handleCallback()
    {
        $accessToken = $this->getService()->requestAccessToken($_GET['code']);

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUserId($securityContext->getToken()->getUser()->getId())
            ->setServiceProviderId($this->getServiceProvider()->getId())
            ->setToken($accessToken->getAccessToken());

        $this->em->persist($accessTokenObj);
        $this->em->flush();
    }

    /**
     * @return AutomaticOAuth2
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return AutomaticOAuth2
     */
    public function createService()
    {
        $credentials = new Credentials(
            $this->consumerKey,
            $this->consumerSecret,
            $this->callbackUri
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('AutomaticOAuth2', 'AppBundle\\OAuth\\AutomaticOAuth2');

        // Commented out scopes do not seem to work at this time.
        /** @var AutomaticOAuth2 $service */
        return $service = $serviceFactory->createService('AutomaticOAuth2', $credentials, $this->storage, [
            AutomaticOAuth2::SCOPE_PUBLIC,
            AutomaticOAuth2::SCOPE_USER_PROFILE,
//            AutomaticOAuth2::SCOPE_USER_FOLLOW,
            AutomaticOAuth2::SCOPE_LOCATION,
//            AutomaticOAuth2::SCOPE_CURRENT_LOCATION,
            AutomaticOAuth2::SCOPE_VEHICLE_PROFILE,
            AutomaticOAuth2::SCOPE_VEHICLE_EVENTS,
//            AutomaticOAuth2::SCOPE_VEHICLE_VIN,
            AutomaticOAuth2::SCOPE_TRIP,
            AutomaticOAuth2::SCOPE_BEHAVIOR
        ]);
    }

    /**
     * @param $responseBody
     * @return array
     */
    public function consumeTrips($responseBody)
    {
        $tripEvents = ['events' => []];
        $json = json_decode($responseBody, true);
        foreach ($json as $event) {
            $driveTime = ($event['end_time'] - $event['start_time']) / 1000;
            $tripEvent = [
                'event_time' => date('Y-m-d H:i:s', $event['start_time'] / 1000),
                'measurements' => ['distance' => $event['distance_m'], 'drive_time' => $driveTime]
            ];
            $tripEvents['events'][] = $tripEvent;
        }

        return $tripEvents;
    }

    /**
     * Return a ServiceProvider object for this ApiAdapter
     * @return mixed
     */
    public function getServiceProvider()
    {
        return $provider = $this->em->getRepository('AppBundle:ServiceProvider')
            ->findOneBy(['slug' => Providers::AUTOMATIC]);
    }
}