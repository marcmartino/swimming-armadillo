<?php
namespace AppBundle\ApiAdapter;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractApiAdapter {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

}