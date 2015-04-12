<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 4/11/15
 * Time: 5:03 PM
 */

namespace AppBundle\Insight\Insights;


use AppBundle\Entity\ABTest;
use AppBundle\Entity\Insight;
use AppBundle\Insight\InsightInterface;

class DailyproteinInsight implements InsightInterface {

    /**
     * @param ABTest $abtest
     * @return Insight
     */
    public function getInsight(ABTest $abtest)
    {
        $insight = (new Insight)
            ->setDescription('Ate 1600 fewer grams of protein.');
        return $insight;
    }
}