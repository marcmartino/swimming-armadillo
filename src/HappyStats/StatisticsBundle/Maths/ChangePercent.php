<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 6/2/15
 * Time: 7:17 PM
 */

namespace HappyStats\StatisticsBundle\Maths;

/**
 * Class ChangePercent
 * @package HappyStats\StatisticsBundle\Maths
 */
class ChangePercent {

    /**
     * @param $conversionRateA
     * @param $conversionRateB
     * @return float
     */
    public static function changePercent($conversionRateA, $conversionRateB)
    {
        return ($conversionRateB - $conversionRateA) / $conversionRateA;
    }
}