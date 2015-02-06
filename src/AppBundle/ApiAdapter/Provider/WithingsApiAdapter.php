<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\ApiAdapterInterface;
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
     * @return array
     */
    public function getTranscribedData()
    {
        $expected = [
            'device' => 'withings',
            'measurement' => 'distance walked',
            'units' => 'ft',
            'value' => 5
        ];

        return $expected;
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
}