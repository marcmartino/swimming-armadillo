<?php

namespace Application\Migrations;

use AppBundle\MeasurementType\MeasurementType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create measurement types table and add heart rate
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111111551 extends AbstractMigration implements ContainerAwareInterface
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
        $this->pdo->query("CREATE TABLE measurement_types (id SERIAL, slug VARCHAR(30), name VARCHAR(150))");

        $this->pdo->query("INSERT INTO measurement_types (slug, name) VALUES ('" . MeasurementType::HEART_RATE . "', 'Heart Rate')");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DROP TABLE measurement_types");

    }
}