<?php
namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;

/**
 * Class FOSUserBundleRegistrationInitialized
 * @package AppBundle\EventListener
 */
class FOSUserBundleRegistrationInitialized {

    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @param UserEvent $event
     */
    public function processEvent(UserEvent $event)
    {
        $request = $event->getRequest();
        $formFields = $request->get('fos_user_registration_form');
        $registrationCode = $this->em->getRepository('AppBundle:RegistrationCode')
            ->findOneBy(['code' => $formFields['registrationCodeCode']]);
        if (!empty($registrationCode)) {
            $event->getUser()->setRegistrationCode($registrationCode);
        } else {
            // TODO Handle lock
        }
    }
}