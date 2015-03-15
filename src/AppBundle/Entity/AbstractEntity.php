<?php
namespace AppBundle\Entity;
use Doctrine\DBAL\Driver\Connection;

/**
 * Class AbstractEntity
 * @package AppBundle\Entity
 */
class AbstractEntity
{
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