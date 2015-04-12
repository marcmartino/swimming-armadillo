<?php

namespace AppBundle\Insight;

use AppBundle\Entity\User;

class InsightService {

    /** @var InsightFactory */
    protected $insightFactory;

    public function __construct(InsightFactory $insightFactory)
    {
        $this->insightFactory = $insightFactory;
    }

    public function getInsightSlugs()
    {
        return ['weight', 'dailyprotein'];
    }

    public function getInsights(\AppBundle\Entity\ABTest $abTest, User $user)
    {
        $insights = [];
        foreach ($this->getInsightSlugs() as $insightSlug) {
            $insight = $this->insightFactory->getInsight($insightSlug);
            $insights[] = $insight->getInsight($abTest);
        }
        return $insights;
    }
}