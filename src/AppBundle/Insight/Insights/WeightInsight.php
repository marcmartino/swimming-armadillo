<?php

namespace AppBundle\Insight\Insights;

use AppBundle\Entity\Insight;
use AppBundle\Insight\AbstractInsight;
use AppBundle\Insight\InsightInterface;

class WeightInsight extends AbstractInsight implements InsightInterface {

    /**
     * @param \AppBundle\Entity\ABTest $abtest
     * @return Insight
     */
    public function getInsight(\AppBundle\Entity\ABTest $abtest)
    {
        $startDate = $abtest->getStartDate();
        $endDate = $abtest->getEndDate();

        $firstWeight = $this->gitFirstMeasurementOfType($startDate, 10);
        $lastWeight = $this->getLastMeasurementOfType($endDate, 10);

        $insight = (new Insight)
            ->setDescription('Lost ' . ((int) $firstWeight['units'] - (int) $lastWeight['units']) . ' grams.');

        return $insight;
    }
}