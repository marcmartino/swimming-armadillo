<?php
namespace AppBundle\Controller;

use AppBundle\Entity\MeasurementType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class GraphController
 * @package AppBundle\Controller
 */
class GraphController extends Controller
{
    /**
     * @Route("/graph", name="graph")
     */
    public function graphAction()
    {
        return $this->render('graph/graph.html.twig', [
            'measurement_types' => $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:MeasurementType')
            ->findAll()
        ]);
    }

    /**
     * @Route("/graph/visualization", name="graph_viz")
     */
    public function graphIframeAction()
    {
        return $this->render("graph/graph_iframe.html.twig");
    }

    /**
     * @Route("/graph/measure", name="graph_measurement_type")
     */
    public function graphMeasurementType()
    {
        $measurementTypeSlugs = explode('-', $_GET['measure']);

        $measurementTypes = [];
        foreach ($measurementTypeSlugs as $slug) {
            $measurementTypes[] = $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:MeasurementType')
                ->findOneBy(['slug' => $slug]);
        }

        return $this->render('graph/graph_measurement_type.html.twig', [
            'measurementTypeSlug' => $_GET['measure'],
            'measurement_types' => $measurementTypes,
            'start' => !empty($_GET['start']) ? $_GET['start'] : '',
            'end' => !empty($_GET['end']) ? $_GET['end'] : '',
        ]);
    }
} 