<?php
namespace AppBundle\ApiAdapter\Provider;

use AppBundle\ApiAdapter\AbstractOAuthApiAdapter;
use AppBundle\ApiParser\ApiParserInterface;
use AppBundle\Entity\MeasurementEventRepository;
use AppBundle\Entity\OAuthAccessTokenRepository;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\Persistence\PersistenceInterface;
use DateTime;
use OAuth\OAuth1\Service\ServiceInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use AppBundle\Entity\MeasurementEvent;
use AppBundle\Entity\OAuthAccessToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class FitbitApiAdapter
 * @package AppBundle\ApiAdapter\Provider
 */
class FitbitApiAdapter extends AbstractOAuthApiAdapter
{
    /** @var ApiParserInterface */
    protected $fitbitFood;
    /** @var ApiParserInterface  */
    protected $fitbitBodyFat;
    /** @var ApiParserInterface */
    protected $fitbitWeight;

    /**
     * {@inheritDoc}
     * @param ApiParserInterface $fitbitFood
     * @param ApiParserInterface $fitbitBodyFat
     * @param ApiParserInterface $fitbitWeight
     */
    public function __construct(
        ServiceInterface $httpClient,
        SecurityContextInterface $securityContext,
        PersistenceInterface $persistence,
        ServiceProviderRepository $serviceProviders,
        OAuthAccessTokenRepository $oauthAccessTokens,
        MeasurementEventRepository $measurementEvents,
        ApiParserInterface $fitbitFood,
        ApiParserInterface $fitbitBodyFat,
        ApiParserInterface $fitbitWeight
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
        $this->fitbitFood = $fitbitFood;
        $this->fitbitBodyFat = $fitbitBodyFat;
        $this->fitbitWeight = $fitbitWeight;
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
        $from1 = clone $from;
        $from2 = clone $from;
        $this->consumeFood($from, $to);
        $this->consumeBodyFat($from1, $to);
        $this->consumeWeight($from2, $to);

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
     * @return DateTime
     */
    public function getEndConsumeDateTime()
    {
        return (new DateTime)->modify('-1 day');
    }
}