<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class WhereIsNullClause extends AbstractClause implements ClauseInterface
{

    private bool $isAnd;


    public function __construct(ColumnInterface $column, ParametersCounter $parametersCounter, bool $isAnd)
    {
        $this->column            = $column;
        $this->parametersCounter = $parametersCounter;
        $this->isAnd             = $isAnd;
    }


    protected function makeExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, string $parameterName): FilterExpression
    {
        $expr = $qb->expr()->isNull($column->getFullName($eavSettings));

        return new FilterExpression($expr, [], $this->column->getJoinTables($eavSettings), $this->isAnd);
    }
}