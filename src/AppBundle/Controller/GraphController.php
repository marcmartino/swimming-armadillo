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
        /** @var MeasurementType $measurementTypeService */
        $measurementTypeService = $this->get('entity_measurement_type');

        return $this->render('graph/graph.html.twig', [
            'measurement_types' => $measurementTypeService->getAll()
        ]);
    }

    /**
     * @Route("/graph/measure", name="graph_measurement_type")
     */
    public function graphMeasurementType()
    {
        return $this->render('graph/graph_measurement_type.html.twig', ['measurementTypeSlug' => $_GET['measure']]);
    }
} 