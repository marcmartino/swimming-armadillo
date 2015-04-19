<?php
namespace AppBundle\Controller;


use AppBundle\Correlator\SimpleSlope;
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

        /**
         * <p>Start: {{ start|date('Y-m-d H:i:s') }}</p>
        <p>End: {{ end|date('Y-m-d H:i:s') }}</p>
        <p>Weight Dataset: {{ weight_data|raw }}</p>
        <p>Bodyfat Dataset: {{ bodyfat_data|raw }}</p>
        <p>Correlation: {{ correlation }}</p>
         */

        $start = new DateTime('2015-03-15');
        $end = new DateTime('2015-04-01');
        $weightData = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '9'
            ]
        ];

        $bodyFatData = [
            [
                'timestamp' => '1429479465',
                'units' => '1'
            ],
            [
                'timestamp' => '1432071465',
                'units' => '5'
            ]
        ];

        return $this->render('correlator/index.html.twig', [
            'start' => $start,
            'end' => $end,
            'weight_data' => $weightData,
            'bodyfat_data' => $bodyFatData,
            'correlation' => $correlator->getCorrelation($weightData, $bodyFatData)
        ]);
    }
}