<?php

namespace Application\Migrations;

use AppBundle\MeasurementType\MeasurementType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add all withings measurement types
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111120759 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $pdo;

    public $types = [
        MeasurementType::WEIGHT => 'Weight',
        MeasurementType::HEIGHT => 'Height',
        MeasurementType::FAT_FREE_MASS => 'Fat Free Mass',
        MeasurementType::FAT_RATIO => 'Fat Ratio',
        MeasurementType::FAT_MASS_WEIGHT => 'Fat Mass Weight',
        MeasurementType::DIASTOLIC_BLOOD_PREASSURE => 'Diastolic Blood Pressure',
        MeasurementType::SYSTOLIC_BLOOD_PREASSURE => 'Systolic Blood Pressure',
        MeasurementType::SPO2 => 'SPO2'
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
