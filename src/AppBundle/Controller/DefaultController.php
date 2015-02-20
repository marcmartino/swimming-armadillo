<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->forward('AppBundle:Provider:providers');
        }
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/view/data", name="graphs")
     */
    public function viewDataAction()
    {
        return $this->render("default/view_data.html.twig");
    }
}