<?php
namespace AppBundle\ApiAdapter;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractApiAdapter {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

}
