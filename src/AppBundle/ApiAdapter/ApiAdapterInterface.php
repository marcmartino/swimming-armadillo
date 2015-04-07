<?php
namespace AppBundle\ApiAdapter;

/**
 * Interface ApiAdapterInterface
 * @package AppBundle\ApiAdapter
 */
interface ApiAdapterInterface
{
    /**
     * Return URI for oauth authorization
     * @return string
     */
    public function getAuthorizationUri();

    /**
     * Consume and persist relevant service provider data
     * @return mixed
     */
    public function consumeData();

    /**
     * Handle service provider authentication callback
     * @return mixed
     */
    public function handleCallback();
}