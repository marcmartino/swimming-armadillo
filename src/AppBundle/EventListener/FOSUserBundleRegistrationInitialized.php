<?php
namespace AppBundle\EventListener;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FOSUserBundleRegistrationInitialized
 * @package AppBundle\EventListener
 */
class FOSUserBundleRegistrationInitialized implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_INITIALIZE => 'processEvent'
        ];
    }

    /**
     * @param UserEvent $event
     */
    public function processEvent(UserEvent $event)
    {
        /** @var AppBundle\Entity\User $user */
        $user = $event->getRequest()->getUser();
        echo $user->getName(); exit;
    }
}