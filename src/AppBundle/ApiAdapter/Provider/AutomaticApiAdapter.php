<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storage = $this->container->get('token_storage_session');;

        $this->service = $this->createService();
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
        // TODO: Implement consumeData() method.
    }

    public function handleCallback()
    {
        $this->getService()->getStorage()->retrieveAccessToken('AutomaticOAuth2');

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
}