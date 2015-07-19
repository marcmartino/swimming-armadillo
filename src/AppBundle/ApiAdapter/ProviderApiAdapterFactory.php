<?php
namespace AppBundle\ApiAdapter;
use AppBundle\Entity\User;
use AppBundle\Provider\Providers;
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

    /** @var User */
    protected $user;

    /**
     * @param Container $container
     * @param EntityManager $em
     */
    public function __construct(Container $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * @param $providerSlug
     * @throws \Exception
     * @return ApiAdapterInterface
     */
    public function getApiAdapter($providerSlug)
    {
        if ($providerSlug == Providers::WITHINGS) {
            return $this->container->get('api_adapter.withings');
        } else if ($providerSlug == Providers::AUTOMATIC) {
            return $this->container->get('api_adapter.automatic');
        } else if ($providerSlug == Providers::FITBIT) {
            return $this->container->get('api_adapter.fitbit');
        }
        throw new \Exception('Unknown service provider ' . $providerSlug);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}