<?php
namespace HappyStats\StatisticsBundle\Maths;


class StatisticalSignificance {

    /**
     * @param $conversionRateA
     * @param $conversionRateB
     * @param $standardErrorA
     * @param $standardErrorB
     * @return float
     */
    public static function calculate($conversionRateA, $conversionRateB, $standardErrorA, $standardErrorB)
    {
        return ($conversionRateB - $conversionRateA) /
        sqrt(($standardErrorA*$standardErrorA)+($standardErrorB*$standardErrorB));
    }
}