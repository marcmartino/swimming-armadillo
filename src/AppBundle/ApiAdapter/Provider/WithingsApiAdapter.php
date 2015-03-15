<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
use DateTime;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\ServiceFactory;
use AppBundle\OAuth\WithingsOAuth;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;

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
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        $token = $this->getService()->requestRequestToken();

        $authorizationUrl = $this->getService()->getAuthorizationUri(
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
        $uri = 'measure?action=getmeas&userid=';

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        /** @var Provider $provider */
        $provider = $this->container->get('entity_provider');

        /** @var OAuthAccessToken $accessTokenService */
        $accessTokenService = $this->container->get('entity.oauth_access_token');
        $userTokens = $accessTokenService->getOAuthAccessTokenForUserAndServiceProvider(
            $securityContext->getToken()->getUser()->getId(),
            $provider->getProvider(Providers::WITHINGS)[0]['id']
        );

        if (count($userTokens) < 1) {
            throw new \Exception("User has not authenticated service provider: " . Providers::WITHINGS);
        }

        $uri .= $userTokens[0]['foreign_user_id'];

        /** @var MeasurementEvent $measurementEventService */
        $measurementEventService = $this->container->get('entity.measurement_event');
        /** @var Measurement $measurementService */
        $measurementService = $this->container->get('entity.measurement');

        $response = $this->getService()->request($uri);

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

    /**
     * @return \OAuth\OAuth1\Service\AbstractService
     */
    public function getService()
    {
        return $this->withingsService;
    }

    /**
     * Capture and store the access token from oauth handshake callback
     */
    public function handleCallback()
    {
        $this->getService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $this->getService()->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $this->storage->retrieveAccessToken('WithingsOAuth')->getRequestTokenSecret()
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
            $provider->getProvider(Providers::WITHINGS)[0]['id'],
            $_GET['userid'],
            $accessToken->getAccessToken(),
            $accessToken->getAccessTokenSecret()
        );
    }

    /**
     * Set user access token in storage
     *
     * @param $accessToken
     * @param $accessTokenSecret
     */
    public function setDatabaseAccessToken($accessToken, $accessTokenSecret)
    {
        $token = new StdOAuth1Token();
        $token->setAccessToken($accessToken);
        $token->setAccessTokenSecret($accessTokenSecret);
        $this->storage->storeAccessToken('WithingsOAuth', $token);
    }
}