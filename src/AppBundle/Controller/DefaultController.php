<?php

namespace AppBundle\Controller;

use AppBundle\Entity\OAuthAccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
//    public function indexAction()
//    {
//        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
//            /** @var OAuthAccessToken $oauthAccessTokenEntity */
//            $oauthAccessTokenEntity = $this->get('entity.oauth_access_token');
//            $oauthTokens = $oauthAccessTokenEntity->getUserOAuthAccessTokens($this->getUser()->getId());
//            if (count($oauthTokens) === 0) {
//                // If the user has not already set up their service providers
//                return $this->forward('AppBundle:Provider:providers');
//            }
//            // Otherwise forward them to the graph page
//            return $this->forward('AppBundle:Graph:graph');
//        }
//        return $this->render('default/index.html.twig');
//    }
}