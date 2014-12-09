<?php
namespace AppBundle\Tests\ApiAdapter;

use GuzzleHttp\Client;
use GuzzleHttp\Message\MessageFactory;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit_Framework_TestCase;
use AppBundle\ApiAdapter\WithingsApiAdapter;

class WithingsApiAdapterTest extends PHPUnit_Framework_TestCase {

    public function testGetTranscribedData()
    {
        $client = new Client();
        $mock = new Mock();
        $f = new MessageFactory();

        $response = $f->createResponse(200, ['foo' => 'bar'], file_get_contents(__DIR__ . '/Resource/mockMeasureResponseDay.json'), [
            'protocol_version' => 1.0
        ]);

        $mock->addResponse($response);

        $client->getEmitter()->attach($mock);

        $adapter = new WithingsApiAdapter($client);

        $actual = $adapter->getTranscribedData();

        $expected = [
            'device' => 'withings',
            'measurement' => 'distance walked',
            'units' => 'ft',
            'value' => 4600
        ];

        $this->assertEquals($expected, $actual);
    }

}
 