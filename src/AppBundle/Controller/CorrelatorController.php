<?php
namespace AppBundle\Controller;


use AppBundle\Correlator\SimpleSlope;
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
        $correlator = $this->get('correlator.simple_slope');

        $start = new DateTime('2015-02-15');
        $end = new DateTime('2015-04-01');

        /** @var UserData $userData */
        $userData = $this->get('user_data');

        $weightUserData = $userData->getUserData(MeasurementType::WEIGHT, $start, $end);

        $weightData = [
            [
                'timestamp' => (new DateTime($weightUserData[0]['event_time']))->getTimestamp(),
                'units' => $weightUserData[0]['units']
            ],
            [
                'timestamp' => (new DateTime(end($weightUserData)['event_time']))->getTimestamp(),
                'units' => end($weightUserData)['units']
            ]
        ];

        $bodyfatUserData = $userData->getUserData(MeasurementType::FAT_RATIO, $start, $end);

        $bodyFatData = [
            [
                'timestamp' => (new DateTime($bodyfatUserData[0]['event_time']))->getTimestamp(),
                'units' => $bodyfatUserData[0]['units']
            ],
            [
                'timestamp' => (new DateTime(end($bodyfatUserData)['event_time']))->getTimestamp(),
                'units' => end($bodyfatUserData)['units']
            ]
        ];

        return $this->render('correlator/index.html.twig', [
            'start' => $start,
            'end' => $end,
            'weight_data' => $weightData,
            'weight_slope' => $correlator->calculateSlopOfDataSet($weightData),
            'bodyfat_data' => $bodyFatData,
            'bodyfat_slope' => $correlator->calculateSlopOfDataSet($bodyFatData),
            'correlation' => $correlator->getCorrelation($weightData, $bodyFatData)
        ]);
    }
}