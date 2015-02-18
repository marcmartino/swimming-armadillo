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
     * @return array
     */
    public function getTranscribedData();

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