<?php

namespace App\EventSubscriber;

use App\Service\AuditLogger;
use App\Enum\ActionType;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuditLogger $auditLogger,
        private LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LogoutEvent::class       => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!is_object($user)) {
            $this->logger->warning('LoginSuccessEvent fired but no user object.');
            return;
        }

        $this->logger->info('🟢 LoginSuccessEvent fired for user '.$user->getUserIdentifier());

        // ✅ LOGIN audit — no old_data, no new_data
        $this->auditLogger->log(
            $user->getId(),      // targetId
            ActionType::LOGIN,
            null,                // old_data
            null                 // new_data
        );
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        $user  = $token?->getUser();

        if (!is_object($user)) {
            $this->logger->warning('LogoutEvent fired but user is not an object.');
            return;
        }

        $this->logger->info('🔴 LogoutEvent fired for user '.$user->getUserIdentifier());

        // ✅ LOGOUT audit — no old_data, no new_data
        $this->auditLogger->log(
            $user->getId(),      // targetId
            ActionType::LOGOUT,
            null,                // old_data
            null                 // new_data
        );
    }
}



