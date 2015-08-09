<?php
namespace AppBundle\EventListener;

use Mandrill;
use Mandrill_Error;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 * Class FOSUserBundleRegistrationCompleted
 * @package AppBundle\EventListener
 */
class FOSUserBundleRegistrationCompleted
{
    /** @var Mandrill */
    protected $mandrill;
    /** @var RouterInterface  */
    protected $router;

    /**
     * @param Mandrill $mandrill
     * @param RouterInterface $router
     * @internal param ContainerInterface $container
     */
    public function __construct(
        Mandrill $mandrill,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->mandrill = $mandrill;
        $this->router = $router;
    }

    /**
     * Send a welcome email to newly registered users
     *
     * @param FilterUserResponseEvent $event
     * @throws Mandrill_Error
     * @throws \Exception
     */
    public function sendWelcomeEmail(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        try {
            $template_name = 'RegistrationConfirmation';
            $template_content = [
                [
                    'name' => $user->getEmail(),
                    'content' => 'Hello There'
                ]
            ];
            $message = [
                'subject' => 'Welcome to HappyStats!',
                'text' => 'html',
                'from_email' => 'marc@happystats.io',
                'to' => [
                    [
                        'email' => $user->getEmail()
                    ]
                ],
                'global_merge_vars' => [
                    [
                        'name' => 'email_address',
                        'content' => $user->getEmail()
                    ]
                ]
            ];

            $this->mandrill->messages->sendTemplate($template_name, $template_content, $message);
        } catch(Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
        }
    }
}