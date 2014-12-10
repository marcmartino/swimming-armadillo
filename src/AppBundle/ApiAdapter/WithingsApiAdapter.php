<?php
namespace AppBundle\ApiAdapter;
use GuzzleHttp\Client;

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
}