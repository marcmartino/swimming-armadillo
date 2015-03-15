<?php

namespace Application\Migrations;

use AppBundle\UnitType\UnitType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111120801 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $pdo;

    public $types = [
        UnitType::PERCENT => 'Percent',
        UnitType::GRAMS => 'Grams',
        UnitTYpe::METERS => 'Meters',
        UnitType::MILLIMETERS_MERCURY => 'Millimeters Mercury',
        UnitType::SECONDS => 'Seconds'
    ];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->pdo = $this->container->get("doctrine.dbal.default_connection");
    }

    public function up(Schema $schema)
    {
        $stmt = $this->pdo->prepare("INSERT INTO unit_types (slug, name) VALUES (:slug, :name)");

        foreach ($this->types as $slug => $name) {
            $stmt->execute([':slug' => $slug, ':name' => $name]);
        }
    }

    public function down(Schema $schema)
    {
        $stmt = $this->pdo->prepare("DELETE FROM unit_types WHERE slug = :slug");

        foreach ($this->types as $slug => $name) {
            $stmt->execute([':slug' => $slug]);
        }
    }
}