<?php

namespace AppBundle\Controller;

use OAuth\ServiceFactory;
use OAuth\Common\Storage\Session;
use AppBundle\OAuth\WithingsOAuth;
use OAuth\Common\Consumer\Credentials;
use AppBundle\ApiAdapter\WithingsApiAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        echo "Hello There";
        exit;
    }

    /**
     * @Route("/withings/callback")
     */
    public function withingsCallback()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $withingsAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $withingsAdapter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

        $conn = $this->get('database_connection');
        $query = "INSERT INTO oauth_access_tokens (user_id, token, secret) VALUES ('" . $_GET['userid'] . "', '" . $accessToken->getAccessToken() . "', '" . $accessToken->getAccessTokenSecret() . "')";
        $conn->query($query);

        return $this->render('default/callback.html.twig');
    }

    /**
     * @Route("/withings/authorize")
     */
    public function authorizeWithings()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        return $this->render('default/authorize.html.twig', ['authorize_uri' => $withingsAdapter->getAuthorizationUrl()]);
    }

    /**
     * @Route("/withings/data", name="withingsdata")
     */
    public function displayWithingData()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $token = $withingsAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        // TODO un-hardcode user id
        $uri = 'measure?action=getmeas&userid=5702500';

        print_r($withingsAdapter->getWithingsService()->request($uri));
        exit;
    }

    /**
     * @Route("/phpinfo")
     */
    public function showPHPInfo()
    {
        phpinfo();
    }
}
