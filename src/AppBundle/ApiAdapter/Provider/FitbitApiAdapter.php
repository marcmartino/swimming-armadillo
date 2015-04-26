<?php
namespace AppBundle\ApiAdapter\Provider;

use DateTime;
use OAuth\ServiceFactory;
use Doctrine\ORM\EntityManager;
use AppBundle\UnitType\UnitType;
use OAuth\OAuth1\Service\FitBit;
use AppBundle\Provider\Providers;
use AppBundle\Entity\Measurement;
use OAuth\Common\Storage\Session;
use AppBundle\Entity\ServiceProvider;
use OAuth\Common\Consumer\Credentials;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\MeasurementType\MeasurementType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FitbitApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class FitbitApiAdapter implements ApiAdapterInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var FitBit */
    protected $service;
    /** @var Session */
    protected $storage;
    /** @var EntityManager */
    private $em;

    public function __construct(
        ContainerInterface $container,
        EntityManager $em
    )
    {
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');

        $this->service = $this->createService();
        $this->em = $em;
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
        // Consume data for the last day (should be changed)
        $date = (new DateTime)->modify('-1 day');
        while ($date <= (new DateTime)) {

            $uri = '/user/-/foods/log/date/' . $date->format('Y-m-d') . '.json';

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
                $measurementEvent = (new MeasurementEvent)
                    ->setProviderId($this->getServiceProvider()->getId())
                    ->setEventTime($date);
                $this->em->persist($measurementEvent);
                $this->em->flush();

                foreach ([$calories] as $measurementArray) {
//                    foreach ([$calories, $carbs, $fat, $fiber, $protein, $sodium] as $measurement) {

                    $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                        ->findOneBy(['slug' => $measurementArray[1]])->getId();
                    $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                        ->findOneBy(['slug' => $measurementArray[2]])->getId();
                    $measurement = (new Measurement)
                        ->setMeasurementEventId($measurementEvent->getId())
                        ->setMeasurementTypeId($measurementTypeId)
                        ->setUnitsTypeId($unitTypeId)
                        ->setUnits($measurementArray[0]);

                    $this->em->persist($measurement);
                    $this->em->flush();
                }
            }

            $date->modify('+1 day');
        }

        $this->em->flush();

        $uri = '/user/-/body/log/fat/date/' . (new DateTime)->modify('-1 month')->format('Y-m-d') . '/' . (new DateTime)->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);
        $json = json_decode($response, true);


        foreach ($json['fat'] as $fatMeasurement) {

            $measurementEvent = (new MeasurementEvent)
                ->setProviderId($this->getServiceProvider()->getId())
                ->setEventTime(new \DateTime($fatMeasurement['date']));
            $this->em->persist($measurementEvent);
            $this->em->flush();

            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::FAT_RATIO])->getId();
            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::PERCENT])->getId();
            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setMeasurementTypeId($measurementTypeId)
                ->setUnitsTypeId($unitTypeId)
                ->setUnits($fatMeasurement['fat']);

            $this->em->persist($measurement);
        }

        $uri = '/user/-/body/log/weight/date/' . (new DateTime)->modify('-1 month')->format('Y-m-d') . '/' . (new DateTime)->format('Y-m-d') . '.json';
        $response = $this->getService()->request($uri);
        $json = json_decode($response, true);

        foreach ($json['weight'] as $weightMeasurement) {
            $measurementEvent = (new MeasurementEvent)
                ->setProviderId($this->getServiceProvider()->getId())
                ->setEventTime(new \DateTime($weightMeasurement['date']));

            $this->em->persist($measurementEvent);

            $measurementTypeId = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => MeasurementType::WEIGHT])->getId();
            $unitTypeId = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy(['slug' => UnitType::GRAMS])->getId();
            $measurement = (new Measurement)
                ->setMeasurementEventId($measurementEvent->getId())
                ->setMeasurementTypeId($measurementTypeId)
                ->setUnitsTypeId($unitTypeId)
                ->setUnits(($weightMeasurement['weight'] * 1000));

            $this->em->persist($measurement);
            $this->em->flush();
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