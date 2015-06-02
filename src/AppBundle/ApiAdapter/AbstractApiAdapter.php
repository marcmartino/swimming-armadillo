<?php
namespace AppBundle\ApiAdapter;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\Entity\ServiceProvider;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Provider\Providers;
use DateTime;
use OAuth\Common\Service\ServiceInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Class AbstractApiAdapter
 * @package AppBundle\ApiAdapter
 */
abstract class AbstractApiAdapter {
    /** @var ServiceInterface */
    protected $httpClient;
    /** @var AdvancedUserInterface */
    protected $user;
    /** @var PersistenceInterface */
    protected $persistence;
    /** @var ServiceProvider */
    protected $serviceProvider;
    /** @var OAuthAccessTokenRepository */
    protected $oauthAccessTokens;
    /** @var MeasurementEventRepository*/
    protected $measurementEvents;

    public function __construct(
        ServiceInterface $httpClient,
        SecurityContextInterface $securityContext,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents
    )
    {
        $this->httpClient = $httpClient;
        // TODO I don't like this, it would be better to inject the user
        $this->user = $securityContext->getToken()->getUser();
        $this->persistence = $persistence;
        $this->serviceProviders = $serviceProviders;
        $this->oauthAccessTokens = $oauthAccessTokens;
        $this->measurementEvents = $measurementEvents;
    }

    /**
     * @return ServiceInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return AdvancedUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return PersistenceInterface
     */
    public function getPersistence()
    {
        return $this->persistence;
    }

    /**
     * @return ServiceProviderRepository
     */
    public function getServiceProviders()
    {
        return $this->serviceProviders;
    }

    /**
     * Return a service provider entity for fitbit
     *
     * @return null|ServiceProvider
     */
    public function getServiceProvider()
    {
        return $this->serviceProviders->findOneBy(['slug' => Providers::FITBIT]);
    }

    /**
     * @return OAuthAccessTokenRepository
     */
    public function getOauthAccessTokens()
    {
        return $this->oauthAccessTokens;
    }

    /**
     * @return MeasurementEventRepository
     */
    public function getMeasurementEvents()
    {
        return $this->measurementEvents;
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        $token = $this->getHttpClient()->requestRequestToken();
        return $this->getHttpClient()->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    }

    /**
     * @throws UserNotAuthenticatedWithServiceProvider
     * @return null|OauthAccessToken
     */
    public function getUserOauthToken()
    {
        $user = $this->getUser();

        $oauthToken = $this->oauthAccessTokens
            ->findOneBy([
                'user' => $user,
                'serviceProvider' => $this->getServiceProvider()
            ]);

        if (empty($oauthToken)) {
            throw new UserNotAuthenticatedWithServiceProvider("User has not authenticated service provider: " . $this->getServiceProvider()->getSlug());
        }

        return $oauthToken;
    }


    /**
     * Get the date and time to start consuming a service provider's api
     *
     * @return DateTime
     */
    public function getStartConsumeDateTime()
    {
        if($dateTime = $this->getLastMeasurementEventDateTime()) {
            return $dateTime;
        }
        return $this->getDefaultConsumeDateTime();
    }

    /**
     * Get date and time of the most recent data we have for this service provider
     *
     * @return bool|\DateTime
     */
    public function getLastMeasurementEventDateTime()
    {
        /** @var \AppBundle\Entity\MeasurementEvent|bool $lastMeasurementEvent */
        $lastMeasurementEvent = $this->measurementEvents->findOneBy(
            ['user' => $this->getUser(), 'serviceProvider' => $this->getServiceProvider()],
            ['eventTime' => 'DESC']
        );
        if (empty($lastMeasurementEvent)) {
            return false;
        }
        return $lastMeasurementEvent->getEventTime();
    }

    /**
     * Returns default start time for consuming provider apis, override if necessary
     *
     * @return DateTime
     */
    public function getDefaultConsumeDateTime()
    {
        return (new DateTime)->modify('-1 month');
    }

    /**
     * @return DateTime
     */
    public function getEndConsumeDateTime()
    {
        return (new DateTime);
    }
}