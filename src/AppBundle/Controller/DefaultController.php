<?php

namespace AppBundle\Controller;

use AppBundle\ApiAdapter\WithingsApiAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        return $this->render('default/index.html.twig', ['authorize_uri' => $withingsAdapter->getAuthorizationUrl()]);
    }
}
