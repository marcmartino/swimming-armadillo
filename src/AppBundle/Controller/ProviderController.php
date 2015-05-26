<?php
namespace AppBundle\Controller;

use AppBundle\ApiAdapter\ProviderApiAdapterFactory;
use AppBundle\Exception\UserNotAuthenticatedWithServiceProvider;
use OAuth\Common\Exception\Exception as OAuthException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProviderController
 * @package AppBundle\Controller
 */
class ProviderController extends Controller
{
    /**
     * @Route("/providers", name="providers")
     */
    public function providersAction()
    {
        $providers = $this->getDoctrine()->getRepository('AppBundle:ServiceProvider')
            ->findAll();

        return $this->render("provider/providers.html.twig", [
            'providers' => $providers
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
        $apiAdapter->handleCallback();

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
            $this->forward('AppBundle:Provider:authorize', ['slug' => $providerSlug]);
        }

        return new Response();
    }
}