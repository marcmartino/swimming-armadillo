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
	
	$homepageData = [
		'data' => $this->getWeekUserData($measurementTypeData, $userData),

	];
   
     return $this->render('home/index.html.twig', $homepageData);
    }

	private function getWeekUserData($measurementTypes, $userData) {
		
		$weekData = [
			(new \DateTime)->format('Y-m-d') => [],
			(new \DateTime)->modify('-1 day')->format('Y-m-d') => [],
			(new \DateTime)->modify('-2 day')->format('Y-m-d') => [],
			(new \DateTime)->modify('-3 day')->format('Y-m-d') => [],
			(new \DateTime)->modify('-4 day')->format('Y-m-d') => [],
			(new \DateTime)->modify('-5 day')->format('Y-m-d') => [],
			(new \DateTime)->modify('-6 day')->format('Y-m-d') => [],
		];

		$today = new \DateTime();
		$aWeekAgo = (new \DateTime)->modify('-1 week');
		
		foreach ($measurementTypes as $measurementType) {
			$weekUserData = $userData->getUserData($measurementType->getId(),
				$this->getUser()->getId(), $aWeekAgo, $today);

			foreach ($weekUserData as $userDataEntry) {
				$weekKey = date('Y-m-d', strtotime($userDataEntry['event_time']));

				if (!isset($weekData[$weekKey][$measurementType->getName()])) {
					$weekData[$weekKey][$measurementType->getName()] = [
						'measurementObj' => $measurementType,

						'data' => [],
					];
				}
				$weekData[$weekKey][$measurementType->getName()]['data'][] = $userDataEntry;

				
			}
		}
		foreach ($weekData as $dayKey => $dayData) {
			if (count($dayData) == 0)  {
				unset($weekData[$dayKey]);
			}
		}

		return $weekData;
	}
}
