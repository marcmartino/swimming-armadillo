<?php

use AppBundle\Correlator\SimpleSlope;

class SimpleSlopeTest extends PHPUnit_Framework_TestCase {
    public function testGetCorrelation()
    {
        $correlator = new SimpleSlope();

        $dataSet1 = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '3'
            ]
        ];

        $dataSet2 = [
            [
                'timestamp' => '1429479465',
                'units' => '3'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '1'
            ]
        ];

        $correlation = $correlator->getCorrelation($dataSet1, $dataSet2);

        $this->assertEquals(-1, $correlation);



        $dataSet1 = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '5'
            ]
        ];

        $dataSet2 = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '5'
            ]
        ];

        $correlation = $correlator->getCorrelation($dataSet1, $dataSet2);

        $this->assertEquals(1
            , $correlation);



        $dataSet1 = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '9'
            ]
        ];

        $dataSet2 = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '5'
            ]
        ];

        $correlation = $correlator->getCorrelation($dataSet1, $dataSet2);

        $this->assertEquals(2
            , $correlation);
    }
}