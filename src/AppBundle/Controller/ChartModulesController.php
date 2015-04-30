<?php
namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ChartModulesController
 * @package AppBundle\Controller
 */
class ChartModulesController extends Controller
{
    /**
     * @Route('/chartModules/{slug}
     */
    public function moduleAction($slug)
    {
        return $this->render('chartmodules/view.html.twig', ['abtest' => $abTest, 'insights' => $insights]);
    }
}