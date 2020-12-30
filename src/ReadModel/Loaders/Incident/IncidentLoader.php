<?php

namespace App\ReadModel\Loaders\Incident;

use App\Entity\Incident\Incident;
use App\Entity\Security\User;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Loaders\CriteriaInterface;
use App\ReadModel\Loaders\Incident\Criteria\ArrayFilterCriteria;
use App\ReadModel\Loaders\Incident\Criteria\ArraySortingCriteria;
use App\ReadModel\Loaders\Incident\Criteria\BasicPaginationCriteria;
use App\ReadModel\Loaders\Incident\Criteria\UserPermissionsCriteria;
use App\ReadModel\Loaders\PaginationCriteriaInterface;
use App\ReadModel\PaginatedList\PaginatedListResult;
use App\ReadModel\ReadModelBuilder\ReadModelBuilderFactory;
use App\UserActions\UserActionsRightsSetter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class IncidentLoader
{
    private Connection $connection;

    private UserPermissionsCriteria $permissionsCriteria;

    private ReadModelBuilderFactory $readModelBuilderFactory;

    private UserActionsRightsSetter $userActionsRightsSetter;

    public function __construct(
        Connection $connection,
        UserPermissionsCriteria $permissionsCriteria,
        ReadModelBuilderFactory $readModelBuilderFactory,
        UserActionsRightsSetter $userActionsRightsSetter
    )
    {
        $this->connection = $connection;
        $this->permissionsCriteria = $permissionsCriteria;
        $this->readModelBuilderFactory = $readModelBuilderFactory;
        $this->userActionsRightsSetter = $userActionsRightsSetter;
    }


    public function getPaginatedList(
        CriteriaInterface $filterCriteria,
        CriteriaInterface $sortingCriteria,
        PaginationCriteriaInterface $paginationCriteria,
        User $user
    ): PaginatedListResult
    {
        $qb = $this->getBaseQuery();

        $this->permissionsCriteria->applyToIncidentsQuery($qb, $user);
        $filterCriteria->apply($qb);
        $sortingCriteria->apply($qb);

        $total = $this->countTotalRows($qb);

        $paginationCriteria->apply($qb);

        $stmt = $qb->execute();

        $rows = $stmt->fetchAllAssociative();

        $DTOs = $this->readModelBuilderFactory->make($user, $rows, Incident::class, IncidentDTO::class)->build();

        $this->userActionsRightsSetter->setRightsToDTOs($DTOs, $user);

        return new PaginatedListResult($DTOs, $total, $paginationCriteria->getPage(), $paginationCriteria->getPerPage());
    }

    public function getById($id, User $user): ?IncidentDTO
    {
        $result = $this->getPaginatedList(
            new ArrayFilterCriteria(['id' => $id]),
            new ArraySortingCriteria([]),
            new BasicPaginationCriteria(1, 1),
            $user
        );

        return count($result->rows) > 0 ? current($result->rows) : null;
    }


    private function getBaseQuery(): QueryBuilder
    {
        return $this->connection
            ->createQueryBuilder()
            ->from('incidents', 'incidents')
            ->select('incidents.*')
            ->andWhere('incidents.deleted <> true')
            ->groupBy('incidents.id');
    }


    private function countTotalRows(QueryBuilder $qb): int
    {
        $qbSubQuery = clone $qb;

        $qbCountStmt = $this
            ->connection
            ->createQueryBuilder()
            ->from('(' . $qbSubQuery->getSQL() . ') as t')
            ->select('count(*)')
            ->setParameters($qbSubQuery->getParameters(), $qbSubQuery->getParameterTypes())
            ->execute();

        return (int)$qbCountStmt->fetchOne();
    }
}