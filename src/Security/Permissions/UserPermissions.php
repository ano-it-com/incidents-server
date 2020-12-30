<?php

namespace App\Security\Permissions;

use App\Entity\Security\User;

class UserPermissions
{

    private $permissions;

    /**
     * @var User
     */
    private $user;


    public function __construct(User $user, array $permissions)
    {
        $this->permissions = $permissions;
        $this->user        = $user;
    }

    public function getUser(){
        return $this->user;
    }

    public function can(string $permission): bool
    {
        if ( ! isset($this->permissions[$permission])) {
            return false;
        }

        $permissionValue = $this->permissions[$permission];

        if (is_array($permissionValue)) {
            throw new \InvalidArgumentException('\'Can\' method can be used only with simple permissions, not status permissions!');
        }

        return (bool)$permissionValue;
    }


    public function canByStatus(string $permission, string $status): bool
    {
        if ( ! isset($this->permissions[$permission])) {
            return false;
        }

        $permissionValue = $this->permissions[$permission];

        if ( ! is_array($permissionValue)) {
            throw new \InvalidArgumentException('\'canByStatus\' method can be used only with status permissions, not simple permissions!');
        }

        return (bool)($permissionValue[$status] ?? false);
    }


    public function getStatusPermissions(string $code): array
    {

        $permissions = $this->permissions[$code] ?? [];

        if ( ! is_array($permissions)) {
            throw new \InvalidArgumentException($code . ' is not status permission');
        }

        return $permissions;
    }


    public function getUserGroupsIds(): array
    {
        $groups = $this->user->getGroups();

        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->getId();
        }

        return $groupsIds;

    }
}