<?php

namespace Application\Migrations;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Schema\Schema;
use AppBundle\Entity\MeasurementType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use AppBundle\MeasurementType\MeasurementType as MeasurementTypePeer;

/**
 * Add default measurement types
 */
class Version20150111111551 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    /** @var EntityManager */
    private $em;

    public $types = [
        MeasurementTypePeer::DAILY_CALORIES => "Daily Calories",
        MeasurementTypePeer::DAILY_CARBS => "Daily Carbs",
        MeasurementTypePeer::DAILY_FAT => "Daily Fat",
        MeasurementTypePeer::DAILY_FIBER => "Daily Fiber",
        MeasurementTypePeer::DAILY_PROTEIN => "Daily Protein",
        MeasurementTypePeer::DAILY_SODIUM => "Daily Sodium",
        MeasurementTypePeer::DAILY_WATER => "Daily Water",
        MeasurementTypePeer::WEIGHT => 'Weight',
        MeasurementTypePeer::HEIGHT => 'Height',
        MeasurementTypePeer::FAT_FREE_MASS => 'Fat Free Mass',
        MeasurementTypePeer::FAT_RATIO => 'Fat Ratio',
        MeasurementTypePeer::FAT_MASS_WEIGHT => 'Fat Mass Weight',
        MeasurementTypePeer::DIASTOLIC_BLOOD_PREASSURE => 'Diastolic Blood Pressure',
        MeasurementTypePeer::SYSTOLIC_BLOOD_PREASSURE => 'Systolic Blood Pressure',
        MeasurementTypePeer::SPO2 => 'SPO2',
        MeasurementTypePeer::DRIVE_DISTANCE => 'Drive Distance',
        MeasurementTypePeer::DRIVE_TIME => 'Drive Time',
        MeasurementTypePeer::HEART_RATE => 'Heart Rate'
    ];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get("doctrine.orm.entity_manager");
    }

    public function up(Schema $schema)
    {
        foreach ($this->types as $slug => $name) {
            $measuremntType = (new MeasurementType)
                ->setName($name)
                ->setSlug($slug);
            $this->em->persist($measuremntType);
            $this->em->flush();
        }
    }

    public function down(Schema $schema)
    {
        foreach ($this->types as $slug => $name) {
            $measuremntType = $this->em->getRepository('AppBundle:MeasurementType')
                ->findOneBy([
                    'name' => $name,
                    'slug' => $slug
                ]);
            $this->em->remove($measuremntType);
            $this->em->flush();
        }
    }
}