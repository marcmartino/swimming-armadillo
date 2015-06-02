<?php
namespace AppBundle\ApiAdapter\Provider;

use OAuth\OAuth1\Token\StdOAuth1Token;
use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\MeasurementEvent;
use OAuth\Common\Service\ServiceInterface;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiParser\Withings\BodyMeasurement;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class WithingsApiAdapter
 * @package AppBundle\ApiAdapter
 */
class WithingsApiAdapter extends AbstractOAuthApiAdapter
{
    /** @var BodyMeasurement */
    protected $bodyMeasurement;

    /**
     * {@inheritDoc}
     * BodyMeasurement $bodyMeasurement
     */
    public function __construct(
        ServiceInterface $httpClient,
        SecurityContextInterface $securityContext,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents,
        BodyMeasurement $bodyMeasurement
    ) {
        parent::__construct(
            $httpClient,
            $securityContext,
            $persistence,
            $serviceProviders,
            $oauthAccessTokens,
            $measurementEvents
        );

        $this->bodyMeasurement = $bodyMeasurement;
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
        $this->getHttpClient()->getStorage()->storeAccessToken('WithingsOAuth', $token);

        $uri = 'measure?action=getmeas&userid=' . $userOauthToken->getForeignUserId();

        $response = $this->getHttpClient()->request($uri);

        $results = $this->bodyMeasurement->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($results['measurement_events'] as $measurementEvent) {
            $measurementEvent->setServiceProvider($this->getServiceProvider())
                ->setUser($this->getUser());
            $this->getPersistence()->persist($measurementEvent);
        }

        $this->getPersistence()->flush();
    }

    /**
     * Capture and store the access token from oauth handshake callback
     */
    public function handleCallback()
    {
        $this->getHttpClient()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $this->getHttpClient()->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $this->getHttpClient()->getStorage()->retrieveAccessToken('WithingsOAuth')->getRequestTokenSecret()
        );

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUser($this->getUser())
            ->setServiceProvider($this->getServiceProvider())
            ->setForeignUserId($_GET['userid'])
            ->setToken($accessToken->getAccessToken())
            ->setSecret($accessToken->getAccessTokenSecret());

        $this->getPersistence()->persist($accessTokenObj);
        $this->getPersistence()->flush();
    }
}