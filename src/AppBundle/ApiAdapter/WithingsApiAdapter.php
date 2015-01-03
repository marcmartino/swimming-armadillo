<?php
namespace AppBundle\ApiAdapter;

use OAuth\ServiceFactory;
use OAuth\Common\Storage\Memory;
use AppBundle\OAuth\WithingsOAuth;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Storage\TokenStorageInterface;

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
     * @param $consumerKey
     * @param $consumerSecret
     * @param $callbackUri
     * @param TokenStorageInterface $storage
     */
    public function __construct(
        $consumerKey,
        $consumerSecret,
        $callbackUri,
        TokenStorageInterface $storage
    ) {

        $this->callbackUri = $callbackUri;
        $this->consumerSecret = $consumerSecret;
        $this->consumerKey = $consumerKey;
        $this->storage = $storage;

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
     * @return \OAuth\Common\Http\Uri\UriInterface
     */
    public function getAuthorizationUrl()
    {
        $token = $this->getWithingsService()->requestRequestToken();

        $authorizationUrl = $this->getWithingsService()->getAuthorizationUri(
            array('oauth_token' => $token->getRequestToken())
        );

        return $authorizationUrl;
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

}