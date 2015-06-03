<?php
namespace HappyStats\StatisticsBundle\Tests\Maths;

use HappyStats\StatisticsBundle\Maths\ChangePercent;
use HappyStats\StatisticsBundle\Maths\ConfidenceInterval;
use HappyStats\StatisticsBundle\Maths\ConversionRate;
use HappyStats\StatisticsBundle\Maths\StandardError;
use HappyStats\StatisticsBundle\Maths\StatisticalSignificance;
use PHPUnit_Framework_TestCase;

/**
 * Class MathsTest
 * @package HappyStats\StatisticsBundle\Tests\Maths
 */
class MathsTest extends PHPUnit_Framework_TestCase {
    public function testConfidenceInterval()
    {
        $baseLine = [60, 60, 60, 60, 60, 60, 60];
        $control = [60 ,61, 60, 59, 60, 60, 59];
        $test = [100, 60, 90, 80, 100, 80, 80];

        $max = max($baseLine);

        $controlConversions = $this->getConversions($control, $max);
        $testConversions = $this->getConversions($test, $max);

        print_r($max); echo "-"; print_r($controlConversions); echo "-";print_r($testConversions);

        $conversionRateA = ConversionRate::calculate($controlConversions ,count($control));
        $conversionRateB = ConversionRate::calculate($testConversions ,count($test));

        $this->assertEquals((1/7), $conversionRateA);
        $this->assertEquals((7/7), $conversionRateB);

        $changePercent = ChangePercent::changePercent($conversionRateA, $conversionRateB);

        $standardErrorA = StandardError::calculate($conversionRateA, count($control));
        $standardErrorB = StandardError::calculate($conversionRateB, count($test));

        echo "standard error a: " . $standardErrorA;
        echo "standard error b: " . $standardErrorB;

        // Use the confidence and standard error numbers to determine whether users should continue
        // To collect data points for that portion of the a/b test
        $confidenceIntervalA = ConfidenceInterval::calculate($standardErrorA, $conversionRateA);
        $confidenceIntervalB = ConfidenceInterval::calculate($standardErrorB, $conversionRateB);

        echo "confidence level a: " . (int) $confidenceIntervalA;
        echo "confidence level b: " . (int) $confidenceIntervalB;

        $significance = StatisticalSignificance::calculate(
            $conversionRateA,
            $conversionRateB,
            $standardErrorA,
            $standardErrorB
        );

        echo "significance: " . $significance;

        $control = [60,61,60,59,60,60,59];
        $test = [100, 80, 90, 80, 100, 80, 80];
    }

    public function getConversions($dataSet, $max) {
        return array_reduce($dataSet, function ($carry, $item) use ($max) {
            return $carry += (int)($item > $max);
        }, 0);
    }

}