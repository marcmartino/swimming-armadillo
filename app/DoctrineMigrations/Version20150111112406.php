<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create measurement and measurement_event tables
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111112406 extends AbstractMigration implements ContainerAwareInterface
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
        $this->pdo->query("CREATE TABLE measurement_event (id SERIAL, event_time DATETIME, provider_id INT)");

        $this->pdo->query("CREATE TABLE measurement (id SERIAL, measurement_event_id INT, units_type_id INT, units FLOAT)");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DROP TABLE measurement_event");

        $this->pdo->query("DROP TABLE measurement");
    }
}
