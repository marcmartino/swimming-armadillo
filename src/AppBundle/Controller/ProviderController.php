<?php
namespace AppBundle\Controller;


use AppBundle\ApiAdapter\ProviderApiAdapterFactory;
use AppBundle\Entity\Provider;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\Provider\Providers;
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
        $apiAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $apiAdapter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

        /** @var Provider $provider */
        $provider = $this->get('entity_provider');

        $conn = $this->get('database_connection');
        $stmt = $conn->prepare("INSERT INTO oauth_access_tokens (user_id, service_provider_id, foreign_user_id, token, secret) VALUES (:userId, :providerId, :foreignUserId, :accessToken, :accessTokenSecret)");
        $stmt->execute([
            ':userId' => $this->getUser()->getId(),
            ':providerId' => $provider->getProvider(Providers::WITHINGS)[0]['id'],
            ':foreignUserId' => $_GET['userid'],
            ':accessToken' => $accessToken->getAccessToken(),
            ':accessTokenSecret' => $accessToken->getAccessTokenSecret(),
        ]);

        return $this->redirect($this->generateUrl('providers'));
    }
}