<?php
namespace AppBundle\Persistence;

/**
 * Interface PersistenceInterface
 * @package AppBundle\Persistence
 */
interface PersistenceInterface {

    /**
     * @param $entity
     * @return mixed
     */
    public function persist($entity);

    public function flush();
}