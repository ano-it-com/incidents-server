<?php

namespace App\UserActions\ContextFree\Actions;

use App\Entity\Security\User;
use App\Security\Permissions\UserPermissions;
use App\UserActions\ContextFree\ContextFreeUserActionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetResponsibleUsersUserAction implements ContextFreeUserActionInterface
{
    public function exportRights(User $user, UserPermissions $userPermissions): array
    {
        return [
            'canViewResponsibleUser' => function () use ($userPermissions) {
                return $userPermissions->can('is_supervisor') || $userPermissions->can('is_moderator');
            }
        ];
    }

    public function execute(UserInterface $user)
    {
    }
}
