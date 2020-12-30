<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class WhereInClause extends AbstractClause implements ClauseInterface
{

    private array $values;

    private bool $isAnd;


    public function __construct(ColumnInterface $column, array $values, ParametersCounter $parametersCounter, bool $isAnd)
    {
        $this->column            = $column;
        $this->values            = $values;
        $this->parametersCounter = $parametersCounter;
        $this->isAnd             = $isAnd;
    }


    protected function makeExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, string $parameterName): FilterExpression
    {

        $expr = $qb->expr()->in($column->getFullName($eavSettings), ':' . $parameterName);

        return new FilterExpression($expr, [ [ $parameterName, $this->values, Connection::PARAM_STR_ARRAY ] ], $this->column->getJoinTables($eavSettings), $this->isAnd);

    }
}