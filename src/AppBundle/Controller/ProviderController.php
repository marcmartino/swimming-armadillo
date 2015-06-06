<?php
namespace AppBundle\Controller;

use AppBundle\ApiAdapter\ProviderApiAdapterFactory;
use AppBundle\Entity\ServiceProviderRepository;
use AppBundle\Exception\UserNotAuthenticatedWithServiceProvider;
use OAuth\Common\Exception\Exception as OAuthException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class ProviderController
 * @package AppBundle\Controller
 * @Route(service="app.controller.provider_controller")
 */
class ProviderController extends Controller
{
    /**
     * @var ServiceProviderRepository
     */
    protected $serviceProviders;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param ServiceProviderRepository $serviceProviders
     * @param SecurityContextInterface $securityContext
     * @param EngineInterface $templating
     * @param ContainerInterface $container // TODO remove this
     */
    public function __construct(
        ServiceProviderRepository $serviceProviders,
        SecurityContextInterface $securityContext,
        EngineInterface $templating,
        ContainerInterface $container
    ) {
        $this->serviceProviders = $serviceProviders;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->container = $container;
    }

    /**
     * @Route("/providers", name="providers")
     */
    public function providersAction()
    {
        $providers = $this->serviceProviders
            ->findAll();
        /** @var \AppBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();
        $oauthAccessTokens = $user->getOauthAccessTokens();
        $authenticatedProviders = [];
        foreach ($oauthAccessTokens as $token) {
            $authenticatedProviders[] = $token->getServiceProvider()->getId();
        }
        print_r($authenticatedProviders);
        return $this->templating->renderResponse("provider/providers.html.twig", [
            'providers' => $providers,
            'authenticatedProviders' => $authenticatedProviders
        ]);
    }
    /**
     * @Route("/{providerSlug}/authorize", name="providerauthorize")
     */
    public function authorizeAction($providerSlug)
    {
        /** @var ProviderApiAdapterFactory $factory */
        $factory = $this->get('api_adapter_factory');
        $factory->setUser($this->getUser());
        $apiAdapter = $factory->getApiAdapter($providerSlug);
        return new RedirectResponse((string) $apiAdapter->getAuthorizationUri());
    }
    /**
     * @Route("/{providerSlug}/callback")
     */
    public function providerCallback($providerSlug)
    {
        /** @var ProviderApiAdapterFactory $factory */
        $factory = $this->get('api_adapter_factory');
        $factory->setUser($this->getUser());
        $apiAdapter = $factory->getApiAdapter($providerSlug);
        // Handle the callback (store oauth token, ..., ...)
        $oauthToken = !empty($_GET['oauth_token']) ? $_GET['oauth_token'] : null;
        $oauthVerifier = !empty($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : null;
        $apiAdapter->handleCallback($oauthToken, $oauthVerifier);
        // Store the data associated with this provider
        $apiAdapter->consumeData();
        return $this->redirect($this->generateUrl('providers'));
    }
    /**
     * @Route("/{providerSlug}/consume")
     */
    public function providerConsume($providerSlug)
    {
        /** @var ProviderApiAdapterFactory $factory */
        $factory = $this->get('api_adapter_factory');
        $factory->setUser($this->getUser());
        $apiAdapter = $factory->getApiAdapter($providerSlug);
        try {
            $apiAdapter->consumeData();
        } catch (OAuthException $e) {
            $logger = $this->get('logger');
            $logger->error('Exception caught: (' . get_class($e) . ') ' . $e->getMessage() . ' - '
                . $this->getUser()->getId());
        } catch (UserNotAuthenticatedWithServiceProvider $e) {
            $this->forward('AppBundle:Provider:authorize', ['providerSlug' => $providerSlug]);
        }
        return new Response();
    }
}