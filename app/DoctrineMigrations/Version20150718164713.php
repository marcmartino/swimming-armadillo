<?php

namespace Application\Migrations;

use AppBundle\Entity\RegistrationCode;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a development registrationcode (beta code) (should be either 'dev' or 'test')
 */
class Version20150718164713 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    /** @var EntityManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get("doctrine.orm.entity_manager");
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        echo "ENV: " . $this->container->get('kernel')->getEnvironment();
        if(in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'))) {
            $code = new RegistrationCode;
            $code->setCode($this->container->get('kernel')->getEnvironment());
            $this->em->persist($code);
            $this->em->flush();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $code = $this->em->getRepository('AppBundle:RegistrationCode')
            ->findOneBy([
                'code' => $this->container->get('kernel')->getEnvironment()
            ]);
        if (!empty($code)) {
            $this->em->remove($code);
            $this->em->flush();
        }
    }
}
