<?php

namespace Application\Migrations;

use AppBundle\MeasurementType\MeasurementType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150318175659 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $pdo;

    public $types = [
        MeasurementType::DAILY_CALORIES => "Daily Calories",
        MeasurementType::DAILY_CARBS => "Daily Carbs",
        MeasurementType::DAILY_FAT => "Daily Fat",
        MeasurementType::DAILY_FIBER => "Daily Fiber",
        MeasurementType::DAILY_PROTEIN => "Daily Protein",
        MeasurementType::DAILY_SODIUM => "Daily Sodium",
        MeasurementType::DAILY_WATER => "Daily Water"
    ];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->pdo = $this->container->get("doctrine.dbal.default_connection");
    }

    public function up(Schema $schema)
    {
        $stmt = $this->pdo->prepare("INSERT INTO measurement_types (slug, name) VALUES (:slug, :name)");

        foreach ($this->types as $slug => $name) {
            $stmt->execute([':slug' => $slug, ':name' => $name]);
        }
    }

    public function down(Schema $schema)
    {
        $stmt = $this->pdo->prepare("DELETE FROM measurement_types WHERE slug = :slug");

        foreach ($this->types as $slug => $name) {
            $stmt->execute([':slug' => $slug]);
        }
    }
}
