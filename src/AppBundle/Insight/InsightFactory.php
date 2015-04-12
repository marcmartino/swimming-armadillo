<?php

namespace AppBundle\Insight;

use Doctrine\DBAL\Driver\Connection;

class InsightFactory {

    /**
     * @param $insightSlug
     * @return InsightInterface
     */
    public function getInsight($insightSlug)
    {
        $fullyQualifiedName = "AppBundle\\Insight\\Insights\\" . ucfirst($insightSlug) . "Insight";
        return new $fullyQualifiedName();
    }
}