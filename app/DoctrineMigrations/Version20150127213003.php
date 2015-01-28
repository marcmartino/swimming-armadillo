<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add additional columns for oauth_access_tokens
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150127213003 extends AbstractMigration implements ContainerAwareInterface
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
        $this->pdo->query("ALTER TABLE oauth_access_tokens ADD COLUMN foreign_user_id VARCHAR(100)");
        $this->pdo->query("ALTER TABLE oauth_access_tokens ADD CONSTRAINT oauth_access_tokens_user_id_fos_user_id FOREIGN KEY(user_id) REFERENCES fos_user(id);");
        $this->pdo->query("ALTER TABLE oauth_access_tokens ADD COLUMN service_provider_id int");
        $this->pdo->query("ALTER TABLE oauth_access_tokens ADD FOREIGN KEY(service_provider_id) REFERENCES service_providers(id);");
    }

    public function down(Schema $schema)
    {
        $this->pdo->query("ALTER TABLE oauth_access_tokens DROP COLUMN foreign_user_id");
        $this->pdo->query("ALTER TABLE oauth_access_tokens DROP COLUMN service_provider_id");
        $this->pdo->query("ALTER TABLE oauth_access_tokens DROP CONSTRAINT oauth_access_tokens_user_id_fos_user_id;");
    }
}