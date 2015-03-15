<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
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

    /**
     * @return mixed
     */
    public function consumeData()
    {

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