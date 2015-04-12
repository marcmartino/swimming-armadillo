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

            $dateTime = new \DateTime($measurementEvent['event_time']);

            $formattedData[] = [
                'time' => $dateTime->format('Y-m-d g:i A'),
                'units' => $measurementEvent['units'],
            ];

        }

        $response = $this->render('userdata/user_data.html.twig', ['measurement_events' => $formattedData]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
} 