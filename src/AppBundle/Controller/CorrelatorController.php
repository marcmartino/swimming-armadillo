<?php
namespace AppBundle\Controller;


use AppBundle\Correlator\SimpleSlope;
use AppBundle\UserData\UserData;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class CorrelatorController extends Controller {

    /**
     * @Route("/correlator", name="correlator")
     */
    public function correlatorAction()
    {
        return $this->render('correlator/correlator.html.twig', [
            'measurement_types' => $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:MeasurementType')
                ->findAll()
        ]);
    }

    /**
     * @Route("/correlatorcalc", name="correlatorcalc")
     */
    public function calcAction(Request $request)
    {
        $measurementTypeSlugs = explode('-', $_GET['measure']);
        $start = $request->query->get('start', null);
        $end = $request->query->get('end', null);

        if (!empty($start)) {
            $start = new DateTime($start);
        }
        if (!empty($end)) {
            $end = new DateTime($end);
        }

        /** @var SimpleSlope $correlator */
        $correlator = $this->get('correlator.pearson');
        /** @var UserData $userData */
        $userData = $this->get('user_data');

        if (count($measurementTypeSlugs) !== 2) {
            throw new InvalidArgumentException("Two and only two measurement types can be compared for correlations.");
        }

        $measurementType1 = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => $measurementTypeSlugs[0]]);
        $weightUserData = $userData->getUserData($measurementType1->getId(), $start, $end);
        array_walk($weightUserData, function (&$item) {
            $item['timestamp'] = new DateTime($item['event_time']);
        });

        $measurementType2 = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:MeasurementType')
            ->findOneBy(['slug' => $measurementTypeSlugs[1]]);
        $bodyfatUserData = $userData->getUserData($measurementType2->getId(), $start, $end);
        array_walk($bodyfatUserData, function (&$item) {
            $item['timestamp'] = new DateTime($item['event_time']);
        });

        $correlation = $correlator->getCorrelation($weightUserData, $bodyfatUserData);
        if ($correlation > 1 || $correlation < -1) {
            throw new Exception("Correlation did not work, invalid correlation ($correlation)");
        }

        $correlationMap = [
            [1 , 'Perfectly Correlated'],
            [.8,  'Highly Correlated'],
            [.5,  'Correlated'],
            [.3,  'Slightly Correlated'],
            [.1,  'Barely Correlated'],
            [0, 'Not Correlated'],
            [-.1, 'Barely Negatively Correlated'],
            [-.3, 'Slightly Negatively Correlated'],
            [-.5, 'Negatively Correlated'],
            [-.8, 'Highly Negatively Correlated'],
            [-1, 'Perfectly Negatively Correlated']
        ];
        $correlationEnglish = 'Error';
        foreach ($correlationMap as $map) {
            if ($correlation >= $map[0]) {
                $correlationEnglish = $map[1];
                break;
            }
        }

        return $this->render('correlator/index.html.twig', [
            'start' => $start,
            'end' => $end,
            'weight_data' => $weightUserData,
            'bodyfat_data' => $bodyfatUserData,
            'correlation' => $correlation,
            'correlationEnglish' => $correlationEnglish,
            'measurementType1' => $measurementType1,
            'measurementType2' => $measurementType2
        ]);
    }
}