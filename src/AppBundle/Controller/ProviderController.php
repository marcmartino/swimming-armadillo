<?php
namespace AppBundle\Controller;


use AppBundle\Entity\Provider;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
        /** @var Provider $provider */
        $provider = $this->get('entity_provider');
        return $this->render("provider/providers.html.twig", [
            'providers' => $provider->getProviders($this->getUser()->getId())
        ]);
    }

    /**
     * @Route("/{providerSlug}/authorize", name="withingsauthorize")
     */
    public function authorizeProvider($providerSlug)
    {
        /** @var ApiAdapterInterface $apiAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $url = $withingsAdapter->getAuthorizationUrl();
        return new RedirectResponse((string) $url);
    }
}