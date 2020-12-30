<?php

namespace App\UserActions\ContextFree\Actions;

use App\Entity\Security\User;
use App\Security\Permissions\UserPermissions;
use App\UserActions\ContextFree\ContextFreeUserActionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetAuthorAndResponsibleGroupUserAction implements ContextFreeUserActionInterface
{
    public function exportRights(User $user, UserPermissions $userPermissions): array
    {
        return [
            'canViewAuthorAndResponsibleGroup' => function () use ($userPermissions) {
                return $userPermissions->can('is_executor');
            }
        ];
    }

    public function execute(UserInterface $user)
    {
    }
}
