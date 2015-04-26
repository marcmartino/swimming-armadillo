<?php

namespace Application\Migrations;

use AppBundle\Entity\UnitType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;
use AppBundle\UnitType\UnitType as UnitTypePeer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Add default unit types
 */
class Version20150111120801 extends AbstractMigration implements ContainerAwareInterface
{
    protected $em;
    protected $container;

    public $types = [
        UnitTypePeer::PERCENT => 'Percent',
        UnitTypePeer::GRAMS => 'Grams',
        UnitTypePeer::METERS => 'Meters',
        UnitTypePeer::MILLIMETERS_MERCURY => 'Millimeters Mercury',
        UnitTypePeer::SECONDS => 'Seconds',
        UnitTypePeer::BEATS_PER_MINUTE => 'Beats Per Minute',
        UnitTypePeer::CALORIES => 'Calories',
        UnitTypePeer::LITERS => 'Liters'
    ];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get("doctrine.orm.entity_manager");
    }

    public function up(Schema $schema)
    {
        foreach ($this->types as $slug => $name) {
            $unitType = (new UnitType)
                ->setName($name)
                ->setSlug($slug);
            $this->em->persist($unitType);
            $this->em->flush();
        }
    }

    public function down(Schema $schema)
    {
        foreach ($this->types as $slug => $name) {
            $unitType = $this->em->getRepository('AppBundle:UnitType')
                ->findOneBy([
                    'name' => $name,
                    'slug' => $slug
                ]);
            $this->em->remove($unitType);
            $this->em->flush();
        }
    }
}