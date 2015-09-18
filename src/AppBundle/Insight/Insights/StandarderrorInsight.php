<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 9/17/15
 * Time: 5:50 PM
 */

namespace AppBundle\Insight\Insights;


use AppBundle\Entity\ABTest;
use AppBundle\Entity\Insight;
use AppBundle\Insight\AbstractInsight;
use AppBundle\Insight\InsightInterface;

/**
 * Class StandardErrorInsight
 * @package AppBundle\Insight\Insights
 */
class StandarderrorInsight extends AbstractInsight implements InsightInterface
{
    /**
     * @param ABTest $abtest
     * @return Insight
     */
    public function getInsight(ABTest $abtest)
    {
        $abtest->getStartDate();
        $abtest->getEndDate();

        // Do maths here
        // Database connection is here:
        $pdo = $this->conn;

        // This will bubble up to the ab test view
        $insight = (new Insight())
            ->setDescription("Test results here.");
        return $insight;
    }
}