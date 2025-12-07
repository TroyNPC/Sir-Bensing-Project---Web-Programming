<?php
namespace App\Security;


use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;


class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }


        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been disabled. Please contact the administrator.'
            );
        }
    }


    public function checkPostAuth(UserInterface $user): void
    {
        // Not needed for now
    }
}




