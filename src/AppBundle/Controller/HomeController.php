<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {

	$userData = $this->get('user_data');
	$measurementTypeData = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')->findAll();
	$weekData = [];
	$today = new \DateTime();
	$aWeekAgo = (new \DateTime)->modify('-1 week');
	foreach ($measurementTypeData as $measurementType) {
	    $weekUserData = $userData->getUserData($measurementType->getId(),
	    		  $this->getUser()->getId(), $aWeekAgo, $today);
	    if (count($weekUserData) > 0) {
	       $updateArray = [
	       		    $measurementType,
			    $weekUserData
];
	       $weekData[] = $updateArray;
	    }
	}
	
	$homepageData = [
	'data' => $weekData,

	];
   
     return $this->render('home/index.html.twig', $homepageData);
    }
}
