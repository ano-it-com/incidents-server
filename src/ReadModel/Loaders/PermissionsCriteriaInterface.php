<?php

namespace App\ReadModel\Loaders;

use App\Entity\Security\User;
use Doctrine\DBAL\Query\QueryBuilder;

interface PermissionsCriteriaInterface
{

    public function applyToIncidentsQuery(QueryBuilder $qb, User $user);


    public function applyToActionsQuery(QueryBuilder $qb, User $user);
}