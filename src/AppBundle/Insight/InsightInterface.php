<?php

namespace AppBundle\Insight;

use AppBundle\Entity\ABTest;
use AppBundle\Entity\Insight;

interface InsightInterface {
    /**
     * @param ABTest $abtest
     * @return Insight
     */
    public function getInsight(ABTest $abtest);
}