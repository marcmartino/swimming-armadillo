<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create table for storing oauth access keys
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141218101730 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $pdo;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->pdo = $this->container->get("doctrine.dbal.default_connection");
    }

    public function up(Schema $schema)
    {
        $this->pdo->query("CREATE TABLE oauth_access_tokens (id int, user_id VARCHAR(100), token VARCHAR(100), secret VARCHAR(100))");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DROP TABLE oauth_access_token");
    }
}
