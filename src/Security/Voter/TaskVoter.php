<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    public const DELETE = 'delete';

    private $security;

    public function __construct(
        Security $security
    ) {
        $this->security = $security;
    }

    public function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            //  An administrator can delete all tasks
            return true;
        }

        return match ($attribute) {
            self::DELETE => $this->canDeleteTask($subject, $user),
            default => false,
        };
    }

    public function canDeleteTask(Task $task, User $user): bool
    {
        return $task->getUser() === $user;
    }
}
