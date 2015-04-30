<?php
namespace AppBundle\EventListener;

use Mandrill;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Mandrill_Error;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class FOSUserBundleRegistrationCompleted
 * @package AppBundle\EventListener
 */
class FOSUserBundleRegistrationCompleted
{
    /** @var ContainerInterface */
    protected $container;
    /** @var RouterInterface  */
    protected $router;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router
    ) {
        $this->container = $container;
        $this->router = $router;
    }

    public function processEvent(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        try {
            $mandrill = new Mandrill($this->container->getParameter('mandrill.api_key'));
            $template_name = 'RegistrationConfirmation';
            $template_content = [
                [
                    'name' => $user->getEmail(),
                    'content' => 'Hello There'
                ]
            ];
            $message = [
                'subject' => 'Welcome to hdlbit!',
                'text' => 'html',
                'from_email' => 'hello@hdlbit.com',
                'to' => [
                    [
                        'email' => $user->getEmail()
                    ]
                ],
                'global_merge_vars' => [
// Enable if we desire email confirmations
//                    [
//                        'name' => 'confirmation_url',
//                        'content' => $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true)
//                    ],
                    [
                        'name' => 'email_address',
                        'content' => $user->getEmail()
                    ]
                ]
            ];

            $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }
    }
}