<?php

namespace Application\Migrations;

use AppBundle\UnitType\UnitType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create unit_types database and add bpm
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111112126 extends AbstractMigration implements ContainerAwareInterface
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
        $this->pdo->query("CREATE TABLE unit_types (id SERIAL, slug VARCHAR(30), name VARCHAR(150))");

        $this->pdo->query("INSERT INTO unit_types (slug, name) VALUES ('" . UnitType::BEATS_PER_MINUTE . "', 'Beats Per Minute')");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DROP TABLE unit_types");

    }
}
