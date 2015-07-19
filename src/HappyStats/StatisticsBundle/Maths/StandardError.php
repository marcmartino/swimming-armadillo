<?php
namespace HappyStats\StatisticsBundle\Maths;

/**
 * Class StandardError
 * @package HappyStats\StatisticsBundle\Maths
 */
class StandardError {
    public static function calculate($converstionRate, $sampleSize)
    {
        return sqrt($converstionRate * (1 - $converstionRate) / $sampleSize);
    }
}