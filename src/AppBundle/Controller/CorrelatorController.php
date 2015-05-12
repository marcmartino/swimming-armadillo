<?php
namespace AppBundle\Controller;


use AppBundle\Correlator\SimpleSlope;
use AppBundle\Entity\Measurement;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UserData\UserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class CorrelatorController extends Controller {
    /**
     * @Route("/correlator", name="correlatorindex")
     */
    public function indexAction(Request $request)
    {
        /** @var SimpleSlope $correlator */
        $correlator = $this->get('correlator.pearson');

        $start = new DateTime('2015-02-15');
        $end = new DateTime('2015-04-01');

        /** @var UserData $userData */
        $userData = $this->get('user_data');

        $measurementType = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => MeasurementType::WEIGHT]);
        $weightUserData = $userData->getUserData($measurementType->getId(), $start, $end);
        array_walk($weightUserData, function (&$item, $i) {
            $item['timestamp'] = new DateTime($item['event_time']);
        });

        $measurementType = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => MeasurementType::FAT_RATIO]);
        $bodyfatUserData = $userData->getUserData($measurementType->getId(), $start, $end);
        array_walk($bodyfatUserData, function (&$item, $i) {
            $item['timestamp'] = new DateTime($item['event_time']);
        });

        return $this->render('correlator/index.html.twig', [
            'start' => $start,
            'end' => $end,
            'weight_data' => $weightUserData,
            'bodyfat_data' => $bodyfatUserData,
            'correlation' => $correlator->getCorrelation($weightUserData, $bodyfatUserData)
        ]);
    }
}