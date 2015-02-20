<?php
namespace AppBundle\Tests\ApiAdapter;

use AppBundle\ApiAdapter\Provider\AutomaticApiAdapter;
use OAuth\Common\Storage\Memory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AutomaticApiAdapterTest
 * @package AppBundle\Tests\ApiAdapter
 */
class AutomaticApiAdapterTest extends WebTestCase
{
    public function testConsumeTrips()
    {
        $client = self::createClient();
        $container = $client->getContainer();
        $container->set('token_storage_session',new Memory());

        $adapter = new AutomaticApiAdapter($container);

        $testResponseBody = file_get_contents(__DIR__ . "/Resource/mockAutomaticTrips.json");

        $expected = [
            'events' => [
                [
                    'event_time' => '2015-02-19 16:06:33',
                    'measurements' => [
                        'distance' => '6573.416666666661'
                    ]
                ]
            ]
        ];

        $actual = $adapter->consumeTrips($testResponseBody);

        $this->assertEquals($expected, $actual);
    }
}