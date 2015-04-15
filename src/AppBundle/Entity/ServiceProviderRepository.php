<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * Class ServiceProviderRepository
 * @package AppBundle\Entity
 */
class ServiceProviderRepository extends EntityRepository
{
    public function findAllForUser($userId)
    {
    }
} 