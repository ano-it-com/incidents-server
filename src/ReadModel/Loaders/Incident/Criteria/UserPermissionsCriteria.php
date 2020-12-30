<?php

namespace App\ReadModel\Loaders\Incident\Criteria;

use App\Entity\Security\User;
use App\ReadModel\Loaders\PermissionsCriteriaInterface;
use App\Security\Permissions\PermissionsProvider;
use App\Security\Permissions\UserPermissions;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class UserPermissionsCriteria implements PermissionsCriteriaInterface
{

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;


    public function __construct(PermissionsProvider $permissionsProvider)
    {

        $this->permissionsProvider = $permissionsProvider;
    }


    public function applyToIncidentsQuery(QueryBuilder $qb, User $user)
    {
        $permissions = $this->permissionsProvider->getUserPermissions($user);

        $this->addIncidentStatusClauses($qb, $permissions);
        $this->addActionStatusClausesForIncidentsQuery($qb, $permissions);
        $this->addCanViewWithoutActionsClausesForIncidentsQuery($qb, $permissions);
        $this->addIsByResponsibleOnlyClausesForIncidentsQuery($qb, $permissions);
    }


    public function applyToActionsQuery(QueryBuilder $qb, User $user)
    {
        $permissions = $this->permissionsProvider->getUserPermissions($user);

        $this->addActionStatusClausesForActionsQuery($qb, $permissions);
        $this->addCanViewWithoutActionsClausesForActionsQuery($qb, $permissions);
        $this->addIsByResponsibleOnlyClausesForActionsQuery($qb, $permissions);
    }


    private function addIncidentStatusClauses(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $permissions = $userPermissions->getStatusPermissions('can_view_incident_by_status');

        if ( ! count($permissions)) {
            //не находить ничего
            $qb->andWhere('incidents.id = 0');

            return;
        }

        $this->leftJoin($qb, 'incidents', 'incident_statuses', 'incident_status', 'incidents.status_id = incident_status.id');

        $parts = [];
        foreach ($permissions as $statusCode => $allow) {
            if ( ! $allow) {
                continue;
            }

            $parts[] = 'incident_status.code = :incident_' . $statusCode;
            $qb->setParameter('incident_' . $statusCode, $statusCode);
        }

        $orExpr = $qb->expr()->or(...$parts);

        $qb->andWhere($orExpr);

    }


    private function leftJoin(QueryBuilder $qb, string $fromTable, string $joinTable, string $alias, string $condition): void
    {
        if ($this->alreadyJoined($qb, $fromTable, $joinTable, $alias, $condition)) {
            return;
        }

        $qb->leftJoin($fromTable, $joinTable, $alias, $condition);

    }


    private function alreadyJoined(QueryBuilder $qb, string $fromTable, string $joinTable, string $alias, string $condition): bool
    {
        $joins = $qb->getQueryPart('join');

        if ( ! isset($joins[$fromTable])) {
            return false;
        }

        foreach ($joins[$fromTable] as $join) {
            if (
                $join['joinTable'] === $joinTable &&
                $join['joinAlias'] === $alias &&
                $join['joinCondition'] === $condition

            ) {
                return true;
            }
        }

        return false;
    }


    private function addActionStatusClausesForIncidentsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $this->leftJoin($qb, 'incidents', 'actions', 'actions', 'incidents.id = actions.incident_id');

        $this->addActionStatusClausesForActionsQuery($qb, $userPermissions);

    }


    private function addCanViewWithoutActionsClausesForIncidentsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {

        $this->leftJoin($qb, 'incidents', 'actions', 'actions', 'incidents.id = actions.incident_id');

        $this->addCanViewWithoutActionsClausesForActionsQuery($qb, $userPermissions);
    }


    private function addIsByResponsibleOnlyClausesForIncidentsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $this->leftJoin($qb, 'incidents', 'actions', 'actions', 'incidents.id = actions.incident_id');

        $this->addIsByResponsibleOnlyClausesForActionsQuery($qb, $userPermissions);

    }


    private function addActionStatusClausesForActionsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $permissions           = $userPermissions->getStatusPermissions('can_view_action_by_status');
        $canViewWithoutActions = $userPermissions->can('can_view_incident_without_actions');

        // TODO refactor
        $fromPart = $qb->getQueryPart('from');
        $table    = $fromPart[0]['table'];

        if ( ! count($permissions)) {
            //не находить ничего
            $qb->andWhere($table . '.id = 0');

            return;
        }

        $this->leftJoin($qb, 'actions', 'action_statuses', 'action_status', 'actions.status_id = action_status.id');

        $parts = [];
        // TODO refactor
        if ($canViewWithoutActions && $table === 'incidents') {
            $parts[] = '(SELECT COUNT(*) FROM actions actions_empty_count WHERE actions_empty_count.incident_id = incidents.id) = 0';
        }

        foreach ($permissions as $statusCode => $allow) {
            if ( ! $allow) {
                continue;
            }

            $parts[] = 'action_status.code = :action_' . $statusCode;
            $qb->setParameter('action_' . $statusCode, $statusCode);
        }

        $orExpr = $qb->expr()->or(...$parts);

        $qb->andWhere($orExpr);

    }


    private function addCanViewWithoutActionsClausesForActionsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $canViewWithoutActions = $userPermissions->can('can_view_incident_without_actions');

        if ( ! $canViewWithoutActions) {
            // если actions нет - не выполнится
            $qb->andWhere('actions.deleted <> true');

        }
    }


    private function addIsByResponsibleOnlyClausesForActionsQuery(QueryBuilder $qb, UserPermissions $userPermissions): void
    {
        $isByResponsibleOnly = $userPermissions->can('can_view_only_as_responsible');

        if ( ! $isByResponsibleOnly) {
            return;
        }

        $this->leftJoin($qb, 'actions', 'action_statuses', 'action_status', 'actions.status_id = action_status.id');
        $this->leftJoin($qb, 'actions', 'action_statuses', 'action_statuses', 'actions.id = action_statuses.action_id');

        $groupsIds = $userPermissions->getUserGroupsIds();

        $qb->andWhere('action_statuses.responsible_group_id in (:groupsIds)')
           ->setParameter('groupsIds', $groupsIds, Connection::PARAM_INT_ARRAY);

    }

}