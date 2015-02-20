<?php
namespace AppBundle\ApiAdapter;
use OAuth\Common\Service\AbstractService;

/**
 * Interface ApiAdapterInterface
 * @package AppBundle\ApiAdapter
 */
interface ApiAdapterInterface
{
    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri();

    /**
     * @return mixed
     */
    public function consumeData();

    public function handleCallback();
}