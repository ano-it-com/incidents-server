<?php

namespace App\ReadModel\Loaders;

use Doctrine\DBAL\Query\QueryBuilder;

interface CriteriaInterface
{

    public function apply(QueryBuilder $queryBuilder): void;
}