<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 4/11/15
 * Time: 7:49 PM
 */

namespace AppBundle\Insight\Insights;


use AppBundle\Entity\ABTest;
use AppBundle\Entity\Insight;
use AppBundle\Insight\AbstractInsight;
use AppBundle\Insight\InsightInterface;

class FatratioInsight extends AbstractInsight implements InsightInterface{

    /**
     * @param ABTest $abtest
     * @return Insight
     */
    public function getInsight(ABTest $abtest)
    {
        $startDate = $abtest->getStartDate();
        $endDate = $abtest->getEndDate();

        $firstWeight = $this->gitFirstMeasurementOfType($startDate, 13);
        $lastWeight = $this->getLastMeasurementOfType($endDate, 13);

        $insight = (new Insight)
            ->setDescription('Lost ' . ((int) $firstWeight['units'] - (int) $lastWeight['units']) . '% bodyfat.');

        return $insight;
    }
}