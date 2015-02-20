<?php

namespace Application\Migrations;

use AppBundle\Provider\Providers;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150218143415 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Connection
     */
    private $pdo;

    private $providerSlug = Providers::AUTOMATIC;

    private $providerName = 'Automatic';

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->pdo = $this->container->get("doctrine.dbal.default_connection");
    }

    public function up(Schema $schema)
    {
        $this->pdo->query("INSERT INTO service_providers (slug, provider_name) VALUES ('" . $this->providerSlug . "', '" . $this->providerName . "')");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DELETE FROM service_providers WHERE slug = '" . $this->providerSlug . "' && provider_name = '" . $this->providerName . "'");
    }
}

