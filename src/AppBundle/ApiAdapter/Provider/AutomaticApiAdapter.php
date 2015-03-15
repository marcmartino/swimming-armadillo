<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementType as MeasurementTypeService;
use AppBundle\Entity\UnitType as UnitTypeService;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
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
class AutomaticApiAdapter implements ApiAdapterInterface
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');;

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
            $measurementEventId = $measurementEventService->store(
                new \DateTime($measurementEvent['event_time']),
                $provider->getProvider(Providers::AUTOMATIC)[0]['id']
            );
            $measurement = $measurementEvent['measurements'];
            // Store drive distance
            $distance = $measurement['distance'];
            $measurementTypeId = $measurementTypeService->getMeasurementType(MeasurementType::DRIVE_DISTANCE)['id'];
            $measurementService->store(
                $measurementEventId,
                $measurementTypeId,
                $this->unitTypeService->getUnitType(UnitType::METERS)['id'],
                $distance
            );
            // Store drive time
            $driveTime = $measurement['drive_time'];
            $measurementTypeId = $measurementTypeService->getMeasurementType(MeasurementType::DRIVE_TIME)['id'];
            $measurementService->store(
                $measurementEventId,
                $measurementTypeId,
                $this->unitTypeService->getUnitType(UnitType::SECONDS)['id'],
                $driveTime
            );
        }


    }

    public function handleCallback()
    {
        $accessToken = $this->getService()->requestAccessToken($_GET['code']);

        /** @var Provider $provider */
        $provider = $this->container->get('entity_provider');

        /** @var OAuthAccessToken $accessTokenService */
        $accessTokenService = $this->container->get('entity.oauth_access_token');

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenService->store(
            $securityContext->getToken()->getUser()->getId(),
            $provider->getProvider(Providers::AUTOMATIC)[0]['id'],
            null,
            $accessToken->getAccessToken(),
            null
        );
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
            '553f2bc0a03cd495b70e',
            'a093caba2cf0cf959acd82732beaca0c648f19ac',
            'http://hdlbit.com/automatic/callback'
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
     * Set user access token in storage
     *
     * @param $accessToken
     */
    public function setDatabaseAccessToken($accessToken)
    {
        $token = new StdOAuth2Token();
        $token->setAccessToken($accessToken);
        $this->storage->storeAccessToken('AutomaticOAuth2', $token);
    }
}