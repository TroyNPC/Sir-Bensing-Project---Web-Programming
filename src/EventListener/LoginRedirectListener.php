<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class LoginRedirectListener
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        // If for some reason no user is logged, abort
        if (!$user) {
            return;
        }

        // If the user DOES NOT have ROLE_ADMIN → Send back to login
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $event->getRequest()->getSession()->getFlashBag()
                ->add('danger', 'You are not allowed to access admin area.');

            $response = new RedirectResponse(
                $this->router->generate('app_login')
            );

            $event->getRequest()->getSession()->save();

            // Replace the response
            $event->getRequest()->attributes->set('_security.login_response', $response);
        }
    }
}
