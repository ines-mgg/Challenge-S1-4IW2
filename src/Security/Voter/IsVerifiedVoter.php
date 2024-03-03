<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class IsVerifiedVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        // This voter is used for the IS_VERIFIED attribute
        return $attribute === 'IS_VERIFIED';
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // If the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check if the user is logged in and is verified
        return $user->isVerified();
    }
}