<?php
namespace AppBundle\ApiParser;
use Doctrine\ORM\EntityManager;

/**
 * Class AbstractEntityApiParser
 * @package AppBundle\ApiParser
 */
class AbstractEntityApiParser
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }
} 