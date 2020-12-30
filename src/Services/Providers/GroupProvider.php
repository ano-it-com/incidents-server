<?php

namespace App\Services\Providers;

use App\Entity\Security\Group;
use App\Repository\Security\GroupRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class GroupProvider
{

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var Connection
     */
    private $connection;


    public function __construct(Connection $connection, GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->connection = $connection;
    }

    /**
     * @return Group[]
     * @throws Exception
     */
    public function getAllCanBeResponsibleForAction(): array
    {
        $permission = 'is_executor';

        $stmt = $this->connection->createQueryBuilder()
            ->select('groups.id')
            ->from('groups')
            ->leftJoin('groups', 'group_permissions', 'gp', 'groups.id = gp.group_id')
            ->leftJoin('gp', 'permissions', 'p', 'gp.permission_id = p.id')
            ->andWhere('p.code = :permissionCode')
            ->setParameter('permissionCode', $permission)->execute();

        $groupIds = $stmt->fetchFirstColumn();
        if(count($groupIds) == 0){
            return [];
        }
        $query = $this->groupRepository->createQueryBuilder('g');
        return $query->where($query->expr()->in('g.id', $groupIds))
            ->orderBy('g.title', 'asc')
            ->getQuery()
            ->getResult();
    }

    public function getForPermission(string $permissionCode)
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('groups.id')
            ->from('groups')
            ->leftJoin('groups', 'group_permissions', 'gp', 'groups.id = gp.group_id')
            ->leftJoin('gp', 'permissions', 'p', 'gp.permission_id = p.id')
            ->andWhere('p.code = :permissionCode')
            ->setParameter('permissionCode', $permissionCode)
            ->execute();

        $groupIds = $stmt->fetchFirstColumn();
        if(count($groupIds) == 0){
            return [];
        }
        return $this->groupRepository->findBy(['id' => $groupIds]);
    }

}