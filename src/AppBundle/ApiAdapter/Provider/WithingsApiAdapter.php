<?php
namespace AppBundle\ApiAdapter\Provider;

use OAuth\ServiceFactory;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use AppBundle\Provider\Providers;
use AppBundle\OAuth\WithingsOAuth;
use AppBundle\Entity\ServiceProvider;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\Common\Consumer\Credentials;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\MeasurementEvent;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\Common\Service\ServiceInterface;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiParser\Withings\BodyMeasurement;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param User $user
     */
    public function __construct(
        ContainerInterface $container,
        EntityManager $em,
        User $user
    ) {
        parent::__construct($container, $em, $user);
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
        $httpClient = new CurlClient();
        $serviceFactory->setHttpClient($httpClient);
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
        $token = new StdOAuth1Token($userOauthToken->getToken());
        $token->setAccessTokenSecret($userOauthToken->getSecret());
        $this->storage->storeAccessToken('WithingsOAuth', $token);

        $uri = 'measure?action=getmeas&userid=' . $userOauthToken->getForeignUserId();

        $response = $this->getService()->request($uri);

        $results = $this->bodyMeasurement->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($results['measurement_events'] as $measurementEvent) {
            $measurementEvent->setServiceProvider($this->getServiceProvider())
                ->setUser($this->getUser());
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

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUser($securityContext->getToken()->getUser())
            ->setServiceProvider($this->getServiceProvider())
            ->setForeignUserId($_GET['userid'])
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
            ->findOneBy(['slug' => Providers::WITHINGS]);
    }
}