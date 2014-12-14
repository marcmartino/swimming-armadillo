<?php
namespace AppBundle\ApiAdapter;


use AppBundle\OAuth\WithingsOAuth;
use GuzzleHttp\Client;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Memory;
use OAuth\Common\Storage\Session;
use OAuth\ServiceFactory;

/**
 * Class WithingsApiAdapter
 * @package AppBundle\ApiAdapter
 */
class WithingsApiAdapter implements ApiAdapterInterface
{

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @return array
     */
    public function getTranscribedData()
    {
        /** @var \GuzzleHttp\Message\Response $response */
        $response = $this->guzzle->get('google.com');

        $json = $response->json();

        $expected = [
            'device' => 'withings',
            'measurement' => 'distance walked',
            'units' => 'ft',
            'value' => $json['body']['distance']
        ];

        return $expected;
    }

    public function getAuthorizationUrl()
    {
        $storage = new Session();

        $credentials = new Credentials(
            '0513f1d73b6dbf44147357f89b6e9c8921d948c4e884e107cdbcc5fb7d',
            'e4dcdceb32b1f54617c17d2223e522e4405346cb62f0c02729350bc8e605',
            'http://cmacnv.org'
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('WithingsOAuth', 'AppBundle\\OAuth\\WithingsOAuth');
        $withingsService = $serviceFactory->createService('WithingsOAuth', $credentials, $storage);
        $token = $withingsService->requestRequestToken();

        $authorizationUrl = $withingsService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));

        return $authorizationUrl;
    }
}