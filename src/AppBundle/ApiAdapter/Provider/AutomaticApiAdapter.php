<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\OAuth2\Service\ServiceInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\ServiceFactory;
use AppBundle\OAuth\AutomaticOAuth2;
use OAuth\Common\Consumer\Credentials;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class AutomaticApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class AutomaticApiAdapter extends AbstractOAuthApiAdapter implements ApiAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        ServiceInterface $httpClient,
        SecurityContextInterface $securityContext,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents
    )
    {
        parent::__construct(
            $httpClient,
            $securityContext,
            $persistence,
            $serviceProviders,
            $oauthAccessTokens,
            $measurementEvents
        );
    }

    /**
     * @return mixed
     */
    public function consumeData()
    {
        $oauthAccessToken = $this->getUserOauthToken();
        $token = new StdOAuth2Token($oauthAccessToken->getToken());
        $this->storage->storeAccessToken('AutomaticOAuth2', $token);

        $response = $this->getHttpClient()->request('/trips');

        $trips = $this->consumeTrips($response);

        foreach ($trips['events'] as $measurementEvent) {
            $measurementEventObj = new MeasurementEvent();
            $eventTime = new \DateTime($measurementEvent['event_time']);
            $measurementEventObj->setEventTime($eventTime)
                ->setServiceProvider($this->getServiceProvider())
                ->setUser($this->getUser());
            $this->em->persist($measurementEventObj);

            $measurement = $measurementEvent['measurements'];
            // Store drive distance
            $distance = $measurement['distance'];
            $measurementObj = new Measurement();
            $unitType = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::METERS]);
            $measurementType = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::DRIVE_DISTANCE]);
            $measurementObj->setMeasurementEvent($measurementEventObj)
                ->setMeasurementType($measurementType)
                ->setUnitType($unitType)
                ->setUnits($distance);
            $this->em->persist($measurementObj);

            // Store drive time
            $driveTime = $measurement['drive_time'];
            $measurementObj = new Measurement();
            $unitType = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::SECONDS]);
            $measurementType = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::DRIVE_TIME]);
            $measurementObj->setMeasurementEvent($measurementEventObj)
                ->setMeasurementType($measurementType)
                ->setUnitType($unitType)
                ->setUnits($driveTime);
            $this->em->persist($measurementObj);
        }
        $this->em->flush();
    }

    public function handleCallback()
    {
        $accessToken = $this->getHttpClient()->requestAccessToken($_GET['code']);

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUser($securityContext->getToken()->getUser())
            ->setServiceProvider($this->getServiceProvider())
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
        $httpClient = new CurlClient();
        $serviceFactory->setHttpClient($httpClient);
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
        return $provider = $this->getServiceProviders()
            ->findOneBy(['slug' => Providers::AUTOMATIC]);
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        return $this->getHttpClient()->getAuthorizationUri();
    }
}