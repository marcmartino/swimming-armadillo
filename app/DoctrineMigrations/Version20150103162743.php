<?php

namespace Application\Migrations;

use AppBundle\Provider\Providers;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Create table for service providers, add Withings as a service provider
 *
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150103162743 extends AbstractMigration implements ContainerAwareInterface
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
        $this->pdo->query("CREATE TABLE service_providers (id SERIAL, slug VARCHAR(30), provider_name VARCHAR(150))");

        $this->pdo->query("INSERT INTO service_providers (slug, provider_name) VALUES ('" . Providers::WITHINGS . "', 'Withings')");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("DROP TABLE service_providers");

    }
}
