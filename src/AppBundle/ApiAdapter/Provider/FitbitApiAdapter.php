<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementType as MeasurementTypeService;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Entity\UnitType as UnitTypeService;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
use DateTime;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Service\FitBit;
use OAuth\ServiceFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class FitbitApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class FitbitApiAdapter implements ApiAdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var FitBit
     */
    protected $service;
    /**
     * @var Session
     */
    protected $storage;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');;

        $this->service = $this->createService();
    }

    protected function createService()
    {
        $credentials = new Credentials(
            '32ae69ab40734adeaaa3e7c099b7b772',
            '2c6aff81110b470fa5d2b94ef0b276a6',
            'http://hdlbit.com/fitbit/callback'
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('FitBit', 'AppBundle\\OAuth\\FitBit');

        /** @var $fitbitService FitBit */
        return $fitbitService = $serviceFactory->createService('FitBit', $credentials, $this->storage);
    }

    /**
     * @return FitBit|void
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        $token = $this->getService()->requestRequestToken();
        return $this->getService()->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    }

    public function consumeData()
    {
        /** @var MeasurementEvent $measurementEventService */
        $measurementEventService = $this->container->get('entity.measurement_event');
        /** @var Measurement $measurementService */
        $measurementService = $this->container->get('entity.measurement');
        /** @var Provider $providerService */
        $providerService = $this->container->get('entity_provider');
        /** @var UnitTypeService $unitType */
        $unitTypeService = $this->container->get('entity_unit_type');
        /** @var MeasurementTypeService $measurementTypeService */
        $measurementTypeService = $this->container->get('entity_measurement_type');

        $date = (new DateTime)->modify('-1 month');
        while ($date <= (new DateTime)) {

            $uri = '/user/-/foods/log/date/' . $date->format('Y-m-d') . '.json';

            /** @var SecurityContext $securityContext */
            $securityContext = $this->container->get('security.context');
            /** @var Provider $provider */
            $provider = $this->container->get('entity_provider');

            /** @var OAuthAccessToken $accessTokenService */
            $accessTokenService = $this->container->get('entity.oauth_access_token');
            $userTokens = $accessTokenService->getOAuthAccessTokenForUserAndServiceProvider(
                $securityContext->getToken()->getUser()->getId(),
                $provider->getProvider(Providers::FITBIT)[0]['id']
            );

            if (count($userTokens) < 1) {
                throw new \Exception("User has not authenticated service provider: " . Providers::FITBIT);
            }

            $response = $this->getService()->request($uri);

            $json = json_decode($response, true);
            $summary = $json['summary'];


            $calories = [$summary['calories'], MeasurementType::DAILY_CALORIES, UnitType::CALORIES];
            $carbs = [$summary['carbs'], MeasurementType::DAILY_CARBS, UnitType::GRAMS];
            $fat = [$summary['fat'], MeasurementType::DAILY_FAT, UnitType::GRAMS];
            $fiber = [$summary['fiber'], MeasurementType::DAILY_FIBER, UnitType::GRAMS];
            $protein = [$summary['protein'], MeasurementType::DAILY_PROTEIN, UnitType::GRAMS];
            $sodium = [$summary['sodium'], MeasurementType::DAILY_SODIUM, UnitType::GRAMS];
            //        $water = [$summary['water'], MeasurementType::DAILY_WATER, UnitType::LITERS]; Actually in "cups/glasses"

            // If something was logged
            if ($calories != 0) {
                $measurementEventId = $measurementEventService->store($date, $providerService->getProvider(Providers::FITBIT)[0]['id']);

                foreach ([$calories, $carbs, $fat, $fiber, $protein, $sodium] as $measurement) {
                    $measurementService->store(
                        $measurementEventId,
                        $measurementTypeService->getMeasurementType($measurement[1])['id'],
                        $unitTypeService->getUnitType($measurement[2])['id'],
                        $measurement[0]
                    );
                }
            }

            $date->modify('+1 day');
        }

        $uri = '/user/-/body/log/fat/date/' . (new DateTime)->modify('-1 month')->format('Y-m-d') . '/' . (new DateTime)->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);
        $json = json_decode($response, true);


        foreach ($json['fat'] as $fatMeasurement) {
            $measurementEventId = $measurementEventService->store(
                new \DateTime($fatMeasurement['date']),
                $providerService->getProvider(Providers::FITBIT)[0]['id']
            );

            $measurementService->store(
                $measurementEventId,
                $measurementTypeService->getMeasurementType(MeasurementType::FAT_RATIO)['id'],
                $unitTypeService->getUnitType(UnitType::PERCENT)['id'],
                $fatMeasurement['fat']
            );
        }

        $uri = '/user/-/body/log/weight/date/' . (new DateTime)->modify('-1 month')->format('Y-m-d') . '/' . (new DateTime)->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);
        $json = json_decode($response, true);

        foreach ($json['weight'] as $weightMeasurement) {
            $measurementEventId = $measurementEventService->store(
                new \DateTime($weightMeasurement['date']),
                $providerService->getProvider(Providers::FITBIT)[0]['id']
            );

            $measurementService->store(
                $measurementEventId,
                $measurementTypeService->getMeasurementType(MeasurementType::WEIGHT)['id'],
                $unitTypeService->getUnitType(UnitType::GRAMS)['id'],
                $weightMeasurement['weight'] * 1000 // kilograms to grams
            );
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

        /** @var Provider $provider */
        $provider = $this->container->get('entity_provider');

        /** @var OAuthAccessToken $accessTokenService */
        $accessTokenService = $this->container->get('entity.oauth_access_token');

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenService->store(
            $securityContext->getToken()->getUser()->getId(),
            $provider->getProvider(Providers::FITBIT)[0]['id'],
            null,
            $accessToken->getAccessToken(),
            $accessToken->getAccessTokenSecret()
        );
    }
}