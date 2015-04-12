<?php

namespace AppBundle\Insight;

use Doctrine\DBAL\Driver\Connection;

class InsightFactory {
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @param Connection|\PDO $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }
    /**
     * @param $insightSlug
     * @return InsightInterface
     */
    public function getInsight($insightSlug)
    {
        $fullyQualifiedName = "AppBundle\\Insight\\Insights\\" . ucfirst($insightSlug) . "Insight";
        return new $fullyQualifiedName($this->conn);
    }
}