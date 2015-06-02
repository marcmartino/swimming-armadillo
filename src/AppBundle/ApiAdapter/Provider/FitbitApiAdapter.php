<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiParser\ApiParserInterface;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\Exception\UserNotAuthenticatedWithServiceProvider;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Provider\Providers;
use DateTime;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Service\ServiceInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use AppBundle\Entity\ServiceProvider;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Class FitbitApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class FitbitApiAdapter
{
    /** @var ServiceInterface */
    protected $httpClient;
    /** @var AdvancedUserInterface */
    protected $user;
    /** @var ApiParserInterface */
    protected $fitbitFood;
    /** @var ApiParserInterface  */
    protected $fitbitBodyFat;
    /** @var ApiParserInterface */
    protected $fitbitWeight;
    /** @var PersistenceInterface */
    private $persistence;
    /** @var ServiceProvider */
    protected $serviceProvider;
    /** @var OAuthAccessTokenRepository */
    protected $oauthAccessTokens;
    /**
     * @var MeasurementEventRepository
     */
    private $measurementEvents;

    /**
     * @param AbstractService $httpClient
     * @param SecurityContextInterface $securityContext
     * @param ApiParserInterface $fitbitFood
     * @param ApiParserInterface $fitbitBodyFat
     * @param ApiParserInterface $fitbitWeight
     * @param PersistenceInterface $persistence
     * @param ServiceProviderRepository $serviceProviders
     * @param OAuthAccessTokenRepository $oauthAccessTokens
     * @param MeasurementEventRepository $measurementEvents
     */
    public function __construct(
        AbstractService $httpClient,
        SecurityContextInterface $securityContext,
        ApiParserInterface $fitbitFood,
        ApiParserInterface $fitbitBodyFat,
        ApiParserInterface $fitbitWeight,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents
    )
    {
        $this->httpClient = $httpClient;
        // TODO I don't like this, it would be better to inject the user
        $this->user = $securityContext->getToken()->getUser();
        $this->fitbitFood = $fitbitFood;
        $this->fitbitBodyFat = $fitbitBodyFat;
        $this->fitbitWeight = $fitbitWeight;
        $this->persistence = $persistence;
        $this->serviceProviders = $serviceProviders;
        $this->oauthAccessTokens = $oauthAccessTokens;
        $this->measurementEvents = $measurementEvents;
    }

    public function consumeData()
    {
        // Ensure the user has authenticated with fitbit
        $userOauthToken = $this->getUserOauthToken();
        $token = new StdOAuth1Token($userOauthToken->getToken());
        $token->setAccessTokenSecret($userOauthToken->getSecret());
        $this->getHttpClient()->getStorage()->storeAccessToken('FitBit', $token);

        // Consume data for the last day (should be changed)
        $from = $this->getStartConsumeDateTime();
        $to = $this->getEndConsumeDateTime();

        // We have to fetch food results with one request per day
        $this->consumeFood($from, $to);
        $this->consumeBodyFat($from, $to);
        $this->consumeWeight($from, $to);

        $this->persistence->flush();
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     */
    public function consumeFood(DateTime $dateFrom, DateTime $dateTo)
    {
        while ($dateFrom <= $dateTo) {
            $uri = '/user/-/foods/log/date/' . $dateFrom->format('Y-m-d') . '.json';
            $response = $this->getHttpClient()->request($uri);

            $fitbitResults = $this->fitbitFood->parse($response);

            /** @var MeasurementEvent $measurementEvent */
            foreach ($fitbitResults['measurement_events'] as $measurementEvent) {
                $measurementEvent->setEventTime($dateFrom)
                    ->setServiceProvider($this->getServiceProvider())
                    ->setUser($this->getUser());
                $this->persistence->persist($measurementEvent);
            }

            $dateFrom->modify('+1 day');
        }
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     */
    public function consumeBodyFat(DateTime $dateFrom, DateTime $dateTo)
    {
        $uri = '/user/-/body/log/fat/date/' . $dateFrom->format('Y-m-d') . '/' . $dateTo->format('Y-m-d') . '.json';
        $response = $this->getHttpClient()->request($uri);
        $bodyfatResults = $this->fitbitBodyFat->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($bodyfatResults['measurement_events'] as $measurementEvent) {
            $measurementEvent->setServiceProvider($this->getServiceProvider());
            $measurementEvent->setUser($this->getUser());
            $this->persistence->persist($measurementEvent);
        }
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     */
    public function consumeWeight(Datetime $dateFrom, DateTime $dateTo)
    {
        $uri = '/user/-/body/log/weight/date/' . $dateFrom->format('Y-m-d') . '/' . $dateTo->format('Y-m-d') . '.json';
        $response = $this->getHttpClient()->request($uri);

        $weightResults = $this->fitbitWeight->parse($response);

        /** @var MeasurementEvent $measurementEvent */
        foreach ($weightResults['measurement_events'] as $measurementEvent) {
            $measurementEvent->setServiceProvider($this->getServiceProvider())
                ->setUser($this->getUser());
            $this->persistence->persist($measurementEvent);
        }
    }

    /**
     * Request and store an access token for the user
     *
     * @param $oauthToken
     * @param $oauthVerifier
     * @return mixed|void
     */
    public function handleCallback($oauthToken, $oauthVerifier)
    {
        $token = $this->getHttpClient()->getStorage()->retrieveAccessToken('FitBit');

        $accessToken = $this->getHttpClient()->requestAccessToken(
            $oauthToken,
            $oauthVerifier,
            $token->getRequestTokenSecret()
        );

        // Store the newly created access token
        $accessTokenObj = (new OAuthAccessToken)
            ->setUser($this->getUser())
            ->setServiceProvider($this->getServiceProvider())
            ->setToken($accessToken->getAccessToken())
            ->setSecret($accessToken->getAccessTokenSecret());

        $this->persistence->persist($accessTokenObj);
        $this->persistence->flush();
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
     * @return AbstractService
     */
    public function getHttpClient()
    {
        return $this->httpClient;
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
     * @return AdvancedUserInterface
     */
    public function getUser()
    {
        return $this->user;
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
    private function getEndConsumeDateTime()
    {
        return (new DateTime);
    }
}