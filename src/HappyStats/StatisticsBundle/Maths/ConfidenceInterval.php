<?php
namespace HappyStats\StatisticsBundle\Maths;

/**
 * Class ConfidenceInterval
 * @package HappyStats\StatisticsBundle\Maths
 */
class ConfidenceInterval {
    const STANDARD_ERROR_DISTRIBUTION = 1.96;

    /**
     * @param $standardError
     * @param $conversionRate
     * @return bool
     */
    public static function calculate($standardError, $conversionRate) {
            return $conversionRate >= abs($standardError * self::STANDARD_ERROR_DISTRIBUTION);
    }
}