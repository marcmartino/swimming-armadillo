<?php
namespace AppBundle\ApiAdapter;
use Symfony\Component\DependencyInjection\Container;

/**
 * Responsible for creating provider objects based on provided slug
 * Place new provider classes in the ./Provider directory with uppercase first letter and ApiAdapter.php appended
 *
 * Class ProviderApiAdapterFactory
 * @package AppBundle\ApiAdapter
 */
class ProviderApiAdapterFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $providerSlug
     * @return ApiAdapterInterface
     */
    public function getApiAdapter($providerSlug)
    {
        $fullyQualifiedName = "AppBundle\\ApiAdapter\\Provider\\" . ucfirst($providerSlug) . "ApiAdapter";
        return new $fullyQualifiedName($this->container);
    }
}