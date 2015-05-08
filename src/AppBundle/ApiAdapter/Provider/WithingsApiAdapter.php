<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractApiAdapter;
use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\ApiParser\Withings\BodyMeasurement;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Entity\ServiceProvider;
use AppBundle\Provider\Providers;
use DateTime;
use Doctrine\ORM\EntityManager;
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
class WithingsApiAdapter extends AbstractOAuthApiAdapter implements ApiAdapterInterface
{
    /** @var TokenStorageInterface */
    private $storage;
    /** @var BodyMeasurement */
    protected $bodyMeasurement;

    /**
     * @param Container $container
     */
    public function __construct(
        Container $container,
        EntityManager $em
    ) {
        parent::__construct($container, $em);
        $this->storage = $this->container->get('token_storage_session');;

        $this->service = $this->createWithingsService();
        $this->em = $em;

        $this->bodyMeasurement = $this->container->get('api_parser.withings_body_measurement');
    }

    /**
     * @return ServiceInterface
     */
    public function createWithingsService()
    {
        $credentials = new Credentials(
            $this->container->getParameter('withings_consumer_key'),
            $this->container->getParameter('withings_consumer_secret'),
            $this->container->getParameter('withings_callback_uri')
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('WithingsOAuth', 'AppBundle\\OAuth\\WithingsOAuth');

        /** @var WithingsOAuth $withingsService */
        return $withingsService = $serviceFactory->createService('WithingsOAuth', $credentials, $this->storage);
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function consumeData()
    {
        // Ensure the user has authenticated with fitbit
        $userOauthToken = $this->getUserOauthToken();

        $uri = 'measure?action=getmeas&userid=' . $userOauthToken->getForeignUserId();

        $response = $this->getService()->request($uri);

        $results = $this->bodyMeasurement->parse($response);

        /** @var $results $measurementEvent */
        foreach ($results['measurement_events'] as $measurementEvent) {
            $measurementEvent->setProviderId($this->getServiceProvider()->getId());
            $this->em->persist($measurementEvent);
        }

        $this->em->flush();
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
     * Return a service provider entity for fitbit
     *
     * @return null|ServiceProvider
     */
    public function getServiceProvider()
    {
        return $provider = $this->em->getRepository('AppBundle:ServiceProvider')
            ->findOneBy(['slug' => Providers::WITHINGS]);
    }
}