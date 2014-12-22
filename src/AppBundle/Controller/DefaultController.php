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
        if (!empty($_GET['userid'])) {
            $storage = new Session();

            $token = $storage->retrieveAccessToken('WithingsOAuth');

            echo PHP_EOL;
                echo "Token: " . $token->getRequestToken();
            echo PHP_EOL .  "Secret: " .
                $token->getRequestTokenSecret();
            echo PHP_EOL;

            $withingsService = $this->getWithingsService();

            // This was a callback request from BitBucket, get the token
            $accessToken = $withingsService->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $token->getRequestTokenSecret()
            );

            $stmt = $this->get("doctrine.dbal.default_connection")->prepare("
                INSERT INTO `oauth_access_tokens`(`token`, `secret`)
                VALUES (:token, :secret)
            ");

            $stmt->execute([
                ':token' => $accessToken->getAccessToken(),
                ':secret' => $accessToken->getAccessTokenSecret()]
            );

            return $this->render(
                'default/data.html.twig',
                ['access_token' => $_GET['oauth_token'],
                    'user_id' => $_GET['userid'],
                    'access_token_secret' => $_GET['oauth_verifier']]
            );
        }

        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        return $this->render('default/index.html.twig', ['authorize_uri' => $withingsAdapter->getAuthorizationUrl()]);
    }

    public function displayWithingData()
    {

    }

    /**
     * @return WithingsOAuth
     * @throws \OAuth\Common\Exception\Exception
     */
    protected function getWithingsService()
    {
        $storage = new Session();

        $credentials = new Credentials(
            '0513f1d73b6dbf44147357f89b6e9c8921d948c4e884e107cdbcc5fb7d',
            'e4dcdceb32b1f54617c17d2223e522e4405346cb62f0c02729350bc8e605',
            'http://hdlbit.com/'
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('WithingsOAuth', 'AppBundle\\OAuth\\WithingsOAuth');

        /** @var WithingsOAuth $withingsService */
        return $withingsService = $serviceFactory->createService('WithingsOAuth', $credentials, $storage);
    }
}
