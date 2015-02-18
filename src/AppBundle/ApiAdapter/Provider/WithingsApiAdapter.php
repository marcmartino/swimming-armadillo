<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use DateTime;
use OAuth\ServiceFactory;
use OAuth\Common\Storage\Memory;
use AppBundle\OAuth\WithingsOAuth;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class WithingsApiAdapter
 * @package AppBundle\ApiAdapter
 */
class WithingsApiAdapter implements ApiAdapterInterface
{
    /**
     * @var string
     */
    private $callbackUri;
    /**
     * @var string
     */
    private $consumerSecret;
    /**
     * @var string
     */
    private $consumerKey;
    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * @var ServiceInterface
     */
    private $withingsService;
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(
        Container $container
    ) {
        $this->container = $container;

        $this->consumerKey = $this->container->getParameter('withings_consumer_key');
        $this->consumerSecret = $this->container->getParameter('withings_consumer_secret');
        $this->callbackUri = $this->container->getParameter('withings_callback_uri');
        $this->storage = $this->container->get('token_storage_session');;

        $this->withingsService = $this->createWithingsService();
        $this->container = $container;
    }

    /**
     * @return ServiceInterface
     */
    public function createWithingsService()
    {
        $credentials = new Credentials(
            $this->consumerKey,
            $this->consumerSecret,
            $this->callbackUri
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('WithingsOAuth', 'AppBundle\\OAuth\\WithingsOAuth');

        /** @var WithingsOAuth $withingsService */
        return $withingsService = $serviceFactory->createService('WithingsOAuth', $credentials, $this->storage);
    }

    /**
     * @param $oauthToken
     * @param $oauthVerifier
     * @return \OAuth\Common\Token\TokenInterface|\OAuth\OAuth1\Token\TokenInterface|string
     */
    public function getAccessToken($oauthToken, $oauthVerifier)
    {
        return $this->getWithingsService()->requestAccessToken(
            $oauthToken,
            $oauthVerifier,
            $this->storage->retrieveAccessToken('WithingsOAuth')->getRequestTokenSecret()
        );
    }

    /**
     * @return AbstractService
     */
    public function getWithingsService()
    {
        return $this->withingsService;
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {

        $token = $this->getWithingsService()->requestRequestToken();

        $authorizationUrl = $this->getWithingsService()->getAuthorizationUri(
            array('oauth_token' => $token->getRequestToken())
        );

        return $authorizationUrl;
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function consumeData()
    {
        /** @var MeasurementEvent $measurementEventService */
        $measurementEventService = $this->container->get('entity.measurement_event');
        /** @var Measurement $measurementService */
        $measurementService = $this->container->get('entity.measurement');

//        $token = $this->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        // TODO un-hardcode user id
        $uri = 'measure?action=getmeas&userid=5575888';

        $response = $this->getWithingsService()->request($uri);

        $json = json_decode($response, true);

        if ($json['status'] !== 0) {
            throw new \Exception("Request was unsuccessful.");
        }

        foreach ($json['body']['measuregrps'] as $measureGroup) {
            $datetime = new DateTime(date("Y-m-d H:i:s", $measureGroup['date']));
            $measurements = $measureGroup['measures'];
            $eventId = $measurementEventService->store($datetime, 1);

            foreach ($measurements as $measurement) {

                $measurementTypeId = false;
                $unitsTypeId = false;
                $units = $measurement['value'];

                switch ($measurement['type']) {
                    case 1:  // weight
                        $measurementTypeId = 2;
                        $unitsTypeId = 3;
                        $units = $measurement['value'];
                        break;
                    case 4:  // height
                        $measurementTypeId = 3;
                        $unitsTypeId = 4;
                        break;
                    case 5:  // fat free mass
                        $measurementTypeId = 4;
                        $unitsTypeId = 3;
                        $units = $measurement['value'];
                        break;
                    case 6:  // fat ratio
                        $measurementTypeId = 5;
                        $unitsTypeId = 2;
                        $units = $measurement['value'] * pow(10, $measurement['unit']);
                        break;
                    case 8:  // fat mass weight
                        $measurementTypeId = 6;
                        $unitsTypeId = 3;
                        break;
                    case 11: // heart pulse
                        $measurementTypeId = 1;
                        $unitsTypeId = 1;
                        break;
                    default:
                        throw new \Exception("Measurement type (" . $measurement['type'] . ") not handled");

                }

                $measurementService->store($eventId, $measurementTypeId, $unitsTypeId, $units);
            }
        }
    }
}