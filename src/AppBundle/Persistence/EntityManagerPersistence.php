<?php
namespace AppBundle\Persistence;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EntityManagerPersistence
 * @package AppBundle\Persistence
 */
class EntityManagerPersistence implements PersistenceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @param $entity
     * @return mixed
     */
    public function persist($entity)
    {
        return $this->em->persist($entity);
    }

    public function flush()
    {
        return $this->em->flush();
    }
}