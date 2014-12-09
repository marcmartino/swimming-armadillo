<?php
namespace AppBundle\Consumer;
use AppBundle\ApiAdapter\ApiAdapterInterface;

/**
 * Class Consumer
 */
class Consumer
{
    /**
     * @var ApiAdapterInterface
     */
    private $adapter;

    public function __construct(ApiAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function storeData()
    {
        $data = $this->adapter->getTranscribedData();


    }
} 