<?php
namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use Mailchimp;
use Mailchimp_Error;

/**
 * Class FOSUserBundleRegistrationInitialized
 * @package AppBundle\EventListener
 */
class FOSUserBundleRegistrationInitialized {

    /** @var EntityManagerInterface */
    protected $em;
    /** @var Mailchimp */
    protected $mailchimp;
    /** @var string - the id of the user mailing list with Mailchimp */
    protected $mailingListId;

    /**
     * @param EntityManagerInterface $em
     * @param Mailchimp $mailchimp
     * @param $mailingListId
     */
    public function __construct(
        EntityManagerInterface $em,
        Mailchimp $mailchimp,
        $mailingListId
    ) {
        $this->em = $em;
        $this->mailchimp = $mailchimp;
        $this->mailingListId = $mailingListId;
    }

    /**
     * @param UserEvent $event
     */
    public function processEvent(UserEvent $event)
    {
        $request = $event->getRequest();
        /** @var \AppBundle\Entity\User $user */
        $user = $event->getUser();
        $formFields = $request->get('fos_user_registration_form');
        // If the form has been submitted
        if (!empty($formFields)) {
            $registrationCode = $this->em->getRepository('AppBundle:RegistrationCode')
                ->findOneBy(['code' => $formFields['registrationCodeCode']]);
            if (!empty($registrationCode)) {
                $user->setRegistrationCode($registrationCode);
            } else {
                // Disable accounts without a valid code
                $user->setEnabled(false);
            }
            $this->addUserToMailchimp($formFields['email']);
        }
    }

    /**
     * Add a user's email to our mailing list
     * @param $email - the user's email to add
     */
    public function addUserToMailchimp($email)
    {
        try {
            $this->mailchimp->lists->subscribe($this->mailingListId, ['email' => $email]);
        } catch (Mailchimp_Error $e) {
            // TODO handle subscription errors
            error_log($e->getMessage());
        }
    }
}