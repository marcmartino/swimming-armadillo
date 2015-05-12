<?php
namespace AppBundle\Controller;


use AppBundle\Correlator\SimpleSlope;
use AppBundle\Entity\Measurement;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\UserData\UserData;
use InvalidArgumentException;
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
        $measurementTypeSlugs = explode('-', $_GET['measure']);
        $start = $request->query->get('start', null);
        $end = $request->query->get('end', null);

        if (!empty($start)) {
            $start = new \DateTime($start);
        }
        if (!empty($endDate)) {
            $end = new \DateTime($end);
        }

        /** @var SimpleSlope $correlator */
        $correlator = $this->get('correlator.pearson');
        /** @var UserData $userData */
        $userData = $this->get('user_data');

        if (count($measurementTypeSlugs) !== 2) {
            throw new InvalidArgumentException("Two and only two measurement types can be compared for correlations.");
        }

        $measurementType = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => $measurementTypeSlugs[0]]);
        $weightUserData = $userData->getUserData($measurementType->getId(), $start, $end);
        array_walk($weightUserData, function (&$item) {
            $item['timestamp'] = new DateTime($item['event_time']);
        });

        $measurementType = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => $measurementTypeSlugs[1]]);
        $bodyfatUserData = $userData->getUserData($measurementType->getId(), $start, $end);
        array_walk($bodyfatUserData, function (&$item) {
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