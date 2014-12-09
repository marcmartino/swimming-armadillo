<?php
namespace AppBundle\ApiAdapter;

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
}