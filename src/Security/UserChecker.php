<?php
namespace App\Security;

use App\Entity\User;
use App\Security\UserChecker;
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
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // user account is expired, the user may be notified
        if (! $user->getVerifiedAt()) 
        {
            throw new CustomUserMessageAccountStatusException("Vous devez vérifier votrer compte par email afin de vous connecter.");
        }
    }
}
