<?php
namespace AppBundle\Controller;
use AppBundle\ABTest\ABTestService;
use AppBundle\Entity\ABTest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ABTestController
 * @package AppBundle\Controller
 */
class ABTestController extends Controller {

    /**
     * @Route("/abtest", name="abtestindex")
     */
    public function indexAction()
    {
        $products = $this->getDoctrine()
            ->getRepository('AppBundle:ABTest')
            ->findBy([], ['endDate' => 'DESC']);
        return $this->render('abtest/index.html.twig', ['abtests' => $products]);
    }

    /**
     * @Route("/abtest/create", name="abtestcreate")
     */
    public function createAction(Request $request)
    {
        $abTest = new ABTest();

        $form = $this->createFormBuilder($abTest)
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('startDate', 'date')
            ->add('endDate', 'date')
            ->add('save', 'submit', array('label' => 'Create Test'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($abTest);
            $em->flush();

            return $this->forward('AppBundle:ABTest:view', ['id' => $abTest->getId()]);
        }

        return $this->render('abtest/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/abtest/{id}", name="abtestview")
     */
    public function viewAction($id)
    {
        /** @var ABTestService $abTestService */
        $abTestService = $this->get('abtest');
        $abTestService->setUser($this->getUser());
        $abTest = $this->getDoctrine()
            ->getRepository('AppBundle:ABTest')
            ->find($id);
        $insights = $abTestService->getInsights($abTest);
        return $this->render('abtest/view.html.twig', ['abtest' => $abTest, 'insights' => $insights]);
    }
}