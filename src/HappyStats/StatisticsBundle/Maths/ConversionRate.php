<?php
namespace HappyStats\StatisticsBundle\Maths;

/**
 * Class ConversionRate
 * @package HappyStats\StatisticsBundle\Maths
 */
class ConversionRate {
    /**
     * @param $conversionEventCount
     * @param $viewEventCount
     * @return int
     */
    public static function calculate($conversionEventCount, $viewEventCount)
    {
        return $conversionEventCount / $viewEventCount;
    }
}