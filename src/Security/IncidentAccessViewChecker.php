<?php

namespace App\Security;

use App\ReadModel\Loaders\Incident\Criteria\UserPermissionsCriteria;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\User\UserInterface;

class IncidentAccessViewChecker
{
    private Connection $connection;

    private UserPermissionsCriteria $userPermissionsCriteria;

    public function __construct(Connection $connection, UserPermissionsCriteria $userPermissionsCriteria)
    {
        $this->connection = $connection;
        $this->userPermissionsCriteria = $userPermissionsCriteria;
    }

    public function checkUserAccessToIncident(UserInterface $user, $incidentId): bool
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->from('incidents', 'incidents')
            ->select('incidents.id')
            ->andWhere('incidents.deleted <> true')
            ->andWhere('incidents.id = :incidentId')
            ->setParameter('incidentId', $incidentId)
            ->setMaxResults(1);

        $this->userPermissionsCriteria->applyToIncidentsQuery($qb, $user);

        $stmt = $qb->execute();
        $rows = $stmt->fetchOne();

        return null !== $rows;
    }

    public function checkUserAccessToAction(UserInterface $user, $actionId): bool
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->from('actions', 'actions')
            ->select('actions.id')
            ->andWhere('actions.deleted <> true')
            ->andWhere('actions.id = :actionId')
            ->setParameter('actionId', $actionId)
            ->setMaxResults(1);

        $this->userPermissionsCriteria->applyToActionsQuery($qb, $user);

        $stmt = $qb->execute();
        $rows = $stmt->fetchOne();

        return null !== $rows;
    }
}