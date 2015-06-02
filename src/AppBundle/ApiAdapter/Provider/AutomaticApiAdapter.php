<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiParser\Automatic\Trips;
use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\OAuth2\Service\ServiceInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\ServiceFactory;
use AppBundle\OAuth\AutomaticOAuth2;
use OAuth\Common\Consumer\Credentials;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class AutomaticApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class AutomaticApiAdapter extends AbstractOAuthApiAdapter implements ApiAdapterInterface
{
    /** @var Trips */
    protected $trips;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        ServiceInterface $httpClient,
        SecurityContextInterface $securityContext,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents,
        Trips $trips
    )
    {
        parent::__construct(
            $httpClient,
            $securityContext,
            $persistence,
            $serviceProviders,
            $oauthAccessTokens,
            $measurementEvents
        );

        $this->trips = $trips;
    }

    /**
     * @return mixed
     */
    public function consumeData()
    {
        $oauthAccessToken = $this->getUserOauthToken();
        $token = new StdOAuth2Token($oauthAccessToken->getToken());
        $this->httpClient->getStorage()->storeAccessToken('AutomaticOAuth2', $token);

        $response = $this->getHttpClient()->request('/trips');

        $results = $this->trips->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($results['measurement_events'] as $measurementEvent) {
            $measurementEvent->setServiceProvider($this->getServiceProvider())
                ->setUser($this->getUser());
            $this->getPersistence()->persist($measurementEvent);
        }

        $this->getPersistence()->flush();
    }

    public function handleCallback()
    {
        $accessToken = $this->getHttpClient()->requestAccessToken($_GET['code']);

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUser($securityContext->getToken()->getUser())
            ->setServiceProvider($this->getServiceProvider())
            ->setToken($accessToken->getAccessToken());

        $this->em->persist($accessTokenObj);
        $this->em->flush();
    }

    /**
     * Return a ServiceProvider object for this ApiAdapter
     * @return mixed
     */
    public function getServiceProvider()
    {
        return $provider = $this->getServiceProviders()
            ->findOneBy(['slug' => Providers::AUTOMATIC]);
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        return $this->getHttpClient()->getAuthorizationUri();
    }
}