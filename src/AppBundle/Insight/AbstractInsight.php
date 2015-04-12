<?php

namespace AppBundle\Insight;

use Doctrine\DBAL\Driver\Connection;

abstract class AbstractInsight {
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
}