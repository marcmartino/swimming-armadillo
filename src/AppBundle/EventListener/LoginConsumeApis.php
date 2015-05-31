<?php
namespace AppBundle\EventListener;

use AppBundle\ApiAdapter\ProviderApiAdapterFactory;
use AppBundle\Exception\ApiParserException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Fetch data from all apis associated with user account at login
 *
 * Class LoginConsumeApis
 * @package AppBundle\EventListener
 */
class LoginConsumeApis
{

    /** @var EntityManagerInterface */
    protected $em;
    /**
     * @var ProviderApiAdapterFactory
     */
    protected $providerApiAdapterFactory;
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManagerInterface $em
     * @param ProviderApiAdapterFactory $providerApiAdapterFactory
     * @param SecurityContextInterface $securityContext
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        ProviderApiAdapterFactory $providerApiAdapterFactory,
        SecurityContextInterface $securityContext,
        LoggerInterface $logger
    )
    {
        $this->em = $em;
        $providerApiAdapterFactory->setUser($securityContext->getToken()->getUser());
        $this->providerApiAdapterFactory = $providerApiAdapterFactory;
        $this->securityContext = $securityContext;
        $this->logger = $logger;
    }

    /**
     * Consume new records since the last time user logged in
     *
     * @param InteractiveLoginEvent $event
     */
    public function processEvent(
        InteractiveLoginEvent $event
    ) {
        /** @var \AppBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();
        $accessTokens = $user->getOauthAccessTokens();
        $serviceProviderRepository = $this->em->getRepository('AppBundle:ServiceProvider');
        // TODO add unique key to user_id.service_provider_id in oauth_access_token table
        $serviceProviderSlugs = [];
        foreach ($accessTokens as $accessToken) {
            $serviceProvider = $serviceProviderRepository->find($accessToken->getServiceProvider()->getId());
            if (!in_array($serviceProvider->getSlug(), $serviceProviderSlugs)) {
                $apiAdapter = $this->providerApiAdapterFactory->getApiAdapter($serviceProvider->getSlug());
                try {
                    $apiAdapter->consumeData();
                } catch (ApiParserException $e) {
                    $this->logger->log('error', $e->getMessage());
                }
            }
            $serviceProviderSlugs[] = $serviceProvider->getSlug();
        }
    }
}