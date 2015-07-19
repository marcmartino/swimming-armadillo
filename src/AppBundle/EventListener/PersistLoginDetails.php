<?php
namespace AppBundle\EventListener;
use AppBundle\Entity\UserLoginRepository;
use AppBundle\Persistence\PersistenceInterface;
use AppBundle\Entity\UserLogin;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class PersistLoginDetails
 * @package AppBundle\EventListener
 */
class PersistLoginDetails
{
    /** @var SecurityContextInterface */
    protected $securityContext;
    /** @var \AppBundle\Persistence\PersistenceInterface */
    protected $persistence;

    /**
     * @param \AppBundle\Persistence\PersistenceInterface $persistence
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        PersistenceInterface $persistence,
        SecurityContextInterface $securityContext
    )
    {
        $this->securityContext = $securityContext;
        $this->persistence = $persistence;
    }

    /**
     * Consume new records since the last time user logged in
     *
     * @param InteractiveLoginEvent $event
     */
    public function processEvent(
        InteractiveLoginEvent $event
    ) {
        /** @var \AppBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();

        $userLogin = new UserLogin;
        $userLogin->setUser($user);

        $this->persistence->persist($userLogin);
        $this->persistence->flush();
    }
}