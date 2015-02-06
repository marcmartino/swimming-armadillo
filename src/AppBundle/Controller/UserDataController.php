<?php
namespace AppBundle\Controller;

use AppBundle\UserData\UserData;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserDataController
 * @package AppBundle\Controller
 */
class UserDataController extends Controller
{
    /**
     * @Route("/userdata/{measurementTypeSlug}", name="userdata")
     */
    public function userData($measurementTypeSlug)
    {
        $formattedData = [];

        /** @var UserData $userData */
        $userData = $this->get('user_data');

        foreach ($userData->getUserData($measurementTypeSlug) as $measurementEvent) {

//            $weight = 0;
//            $fatmass = 0;
//            $leanmass = 0;
//
//            foreach ($measurementEvent['measurements'] as $measurement) {
//                if ($measurement['type'] == 2) {
//                    $weight = $measurement['units'] * 0.00220462;
//                } else if ($measurement['type'] == 4) {
//                    $leanmass = $measurement['units'] * 0.00220462;
//                } else if ($measurement['type'] == 6) {
//                    $fatmass = $measurement['units'] * 0.00220462;
//                }
//            }

            $dateTime = new \DateTime($measurementEvent['event_time']);

            $formattedData[] = [
                'time' => $dateTime->format('Y-m-d g:i A'),
                'units' => $measurementEvent['units'],
            ];

        }

        return $this->render('userdata/user_data.html.twig', ['measurement_events' => $formattedData]);
    }
} 