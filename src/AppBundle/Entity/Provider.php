<?php
namespace AppBundle\Entity;
use Doctrine\DBAL\Connection;

/**
 * Class Provider
 * @package AppBundle\Entity
 */
class Provider
{
    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @param Connection|\PDO $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM service_providers
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
} 