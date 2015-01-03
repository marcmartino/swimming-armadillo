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
//        if (!empty($_GET['userid'])) {
            $storage = new Session();
//
            $token = $storage->retrieveAccessToken('WithingsOAuth');
//
//            echo PHP_EOL;
//                echo "Token: " . $token->getRequestToken();
//            echo PHP_EOL .  "Secret: " .
//                $token->getRequestTokenSecret();
//            echo PHP_EOL;
//
//            $withingsService = $this->getWithingsService();
//
//            // This was a callback request from BitBucket, get the token
//            $accessToken = $withingsService->requestAccessToken(
//                $_GET['oauth_token'],
//                $_GET['oauth_verifier'],
//                $token->getRequestTokenSecret()
//            );
//
////            $stmt = $this->get("doctrine.dbal.default_connection")->prepare("
////                INSERT INTO `oauth_access_tokens`(`token`, `secret`)
////                VALUES (:token, :secret)
////            ");
////
////            $stmt->execute([
////                ':token' => $accessToken->getAccessToken(),
////                ':secret' => $accessToken->getAccessTokenSecret()]
////            );
//
//            return $this->render(
//                'default/data.html.twig',
//                ['access_token' => $_GET['oauth_token'],
//                    'user_id' => $_GET['userid'],
//                    'access_token_secret' => $_GET['oauth_verifier']]
//            );
//        }



    }

    /**
     * @Route("/withings/callback")
     */
    public function withingsCallback()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $withingsAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');
        print_r($withingsAdapter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']));
        print_r($withingsAdapter->getWithingsService()->request('measure?action=getmeas&userid=5702500'));

        exit;
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
     * @Route("/withings/data")
     */
    public function displayWithingData()
    {
        $storage = new Session();

        $token = $storage->retrieveAccessToken('WithingsOAuth');

        /** @var WithingsApiAdapter $withingsAdapter */
        $withings = $this->get('withings_api_adapter');

        $parameters = $withings->getWithingsService()->publicGetBasicAuthorizationHeaderInfo();

        $uri = 'measure?action=getmeas&userid=5702500';

        print_r($withings->getWithingsService()->request($uri));

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
