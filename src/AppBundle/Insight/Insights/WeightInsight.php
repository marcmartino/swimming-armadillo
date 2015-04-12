<?php

namespace AppBundle\Insight\Insights;

use AppBundle\Entity\Insight;
use AppBundle\Insight\InsightInterface;

class WeightInsight implements InsightInterface {

    /**
     * @param \AppBundle\Entity\ABTest $abtest
     * @return Insight
     */
    public function getInsight(\AppBundle\Entity\ABTest $abtest)
    {
        $startDate = $abtest->getStartDate();
        $endDate = $abtest->getEndDate();

        $insight = (new Insight)
            ->setDescription('Lost 10 pounds');

        return $insight;
    }
}