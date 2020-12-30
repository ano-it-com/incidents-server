<?php

namespace App\UserActions;

use App\Entity\Security\User;
use App\Security\Permissions\PermissionsProvider;
use App\UserActions\ContextFree\ContextFreeUserActionsLocator;

class ContextFreeRightsExporter
{
    private ContextFreeUserActionsLocator $contextFreeUserAction;

    private PermissionsProvider $permissionsProvider;

    public function __construct(
        ContextFreeUserActionsLocator $contextFreeUserAction,
        PermissionsProvider $permissionsProvider
    ) {
        $this->contextFreeUserAction = $contextFreeUserAction;
        $this->permissionsProvider               = $permissionsProvider;
    }

    public function export(User $user): array
    {
        $permissions = $this->permissionsProvider->getUserPermissions($user);

        $rights = [];
        foreach ($this->contextFreeUserAction->getAllClasses() as $userActionClass) {
            $userAction = $this->contextFreeUserAction->get($userActionClass);
            foreach ($userAction->exportRights($user, $permissions) as $code => $checkFunc){
                $rights[$code] = $checkFunc();
            }
        }

        return $rights;
    }
}