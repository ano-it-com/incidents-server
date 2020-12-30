<?php

namespace App\Security\Permissions;

use App\Entity\Security\User;
use Doctrine\DBAL\Connection;

class PermissionsProvider
{

    /**
     * @var Connection
     */
    private $connection;


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getUserPermissions(User $user): UserPermissions
    {
        $stmt = $this->connection->createQueryBuilder()
                                 ->from('group_permissions')
                                 ->select('group_permissions.*, permissions.code, permissions.restriction_type')
                                 ->leftJoin('group_permissions', 'permissions', 'permissions', 'group_permissions.permission_id = permissions.id')
                                 ->rightJoin('group_permissions', 'groups', 'groups', 'group_permissions.group_id = groups.id')
                                 ->leftJoin('groups', 'users_groups', 'users_groups', 'users_groups.group_id = groups.id')
                                 ->andWhere('users_groups.user_id = :userId')
                                 ->setParameters([
                                     'userId' => $user->getId(),
                                 ])->execute();

        $rows = $stmt->fetchAllAssociative();

        $permissions = [];

        foreach ($rows as $row) {
            $code = $row['code'];
            if ($row['restriction_type'] === null) {
                $permissions[$code] = true;
                continue;
            }

            try {
                $restrictions = json_decode($row['restriction'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($restrictions as $status => $allow) {
                if (isset($permissions[$code][$status]) && $permissions[$code][$status]) {
                    continue;
                }
                if ($allow) {
                    $permissions[$code][$status] = true;
                }
            }

        }

        return new UserPermissions($user, $permissions);
    }
}