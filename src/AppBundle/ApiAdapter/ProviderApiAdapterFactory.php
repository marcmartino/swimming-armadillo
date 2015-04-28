<?php
namespace AppBundle\ApiAdapter;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @param Container $container
     */
    public function __construct(Container $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * @param $providerSlug
     * @return ApiAdapterInterface
     */
    public function getApiAdapter($providerSlug)
    {
        $fullyQualifiedName = "AppBundle\\ApiAdapter\\Provider\\" . ucfirst($providerSlug) . "ApiAdapter";
        return new $fullyQualifiedName($this->container, $this->em);
    }
}