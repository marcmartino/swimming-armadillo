<?php
namespace AppBundle\ApiAdapter;

use Symfony\Component\DependencyInjection\Container;

class AbstractApiAdapter {

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(
        Container $container
    ) {
        $this->container = $container;
    }

}