<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

interface FilterCriteriaInterface
{

    /**
     * @param QueryBuilder $qb
     *
     * @param EAVSettings  $eavSettings
     *
     * @return FilterExpression[]
     */
    public function getExpressions(QueryBuilder $qb, EAVSettings $eavSettings): array;

}