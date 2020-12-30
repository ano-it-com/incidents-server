<?php

namespace App\UserActions\ContextFree;

use App\Entity\Security\User;
use App\Security\Permissions\UserPermissions;

interface ContextFreeUserActionInterface
{

    public function exportRights(User $user, UserPermissions $userPermissions): array;
}