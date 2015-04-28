<?php

namespace Application\Migrations;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Schema\Schema;
use AppBundle\Entity\ServiceProvider;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Add default service providers
 */
class Version20150415095013 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    /** @var EntityManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get("doctrine.orm.entity_manager");
    }

    protected $serviceProviderMap = [
        [
            'name' => 'Fitbit',
            'slug' => 'fitbit'
        ],
        [
            'name' => 'Automatic',
            'slug' => 'automatic'
        ],
        [
            'name' => 'Withings',
            'slug' => 'withings'
        ]
    ];

    public function postUp(Schema $schema)
    {
        foreach ($this->serviceProviderMap as $sp) {
            $serviceProvider = (new ServiceProvider)
                ->setName($sp['name'])
                ->setSlug($sp['slug']);
            $this->em->persist($serviceProvider);
            $this->em->flush();
        }
    }

    public function postDown(Schema $schema)
    {
        foreach ($this->serviceProviderMap as $sp) {
            $serviceProvider = $this->em->getRepository('AppBundle:ServiceProvider')
                ->findOneBy([
                    'name' => $sp['name'],
                    'slug' => $sp['slug']
            ]);
            $this->em->remove($serviceProvider);
            $this->em->flush();
        }
    }

    public function up(Schema $schema)
    {
    }

    public function down(Schema $schema)
    {
    }
}
