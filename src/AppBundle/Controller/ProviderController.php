<?php
namespace AppBundle\Controller;


use AppBundle\Entity\OAuthAccessToken;
use AppBundle\Entity\Provider;
use AppBundle\Provider\Providers;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\ApiAdapter\ProviderApiAdapterFactory;
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
     * @Route("/{providerSlug}/authorize", name="providerauthorize")
     */
    public function authorizeProvider($providerSlug)
    {
        /** @var ProviderApiAdapterFactory $factory */
        $factory = $this->get('api_adapter_factory');
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
        $apiAdapter = $factory->getApiAdapter($providerSlug);
        
        $apiAdapter->getService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $apiAdapter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

        /** @var Provider $provider */
        $provider = $this->get('entity_provider');

        /** @var OAuthAccessToken $accessTokenService */
        $accessTokenService = $this->get('entity.oauth_access_token');

        // Store the newly created access token
        $accessTokenService->store(
            $this->getUser()->getId(),
            $provider->getProvider(Providers::WITHINGS)[0]['id'],
            $_GET['userid'],
            $accessToken->getAccessToken(),
            $accessToken->getAccessTokenSecret()
        );

        // Store the data associated with this provider
        $apiAdapter->consumeData();

        return $this->redirect($this->generateUrl('providers'));
    }
}