<?php

namespace AppBundle\Controller;

use AppBundle\ApiAdapter\WithingsApiAdapter;
use OAuth\Common\Storage\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

            $serviceFactory = new ServiceFactory();
            $serviceFactory->registerService('WithingsOAuth', 'AppBundle\\OAuth\\WithingsOAuth');
            $withingsService = $serviceFactory->createService('WithingsOAuth', $credentials, $storage);

            // This was a callback request from BitBucket, get the token
            $withingsService->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $token->getRequestTokenSecret()
            );

            // Send a request now that we have access token
            $result = json_decode($withingsService->request('user/repositories'));

            echo('The first repo in the list is ' . $result[0]->name);

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
}
