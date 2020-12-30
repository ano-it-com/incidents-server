<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class WhereClause extends AbstractClause implements ClauseInterface
{

    private string $operator;

    private $value;

    private bool $isAnd;


    public function __construct(ColumnInterface $column, string $operator, $value, ParametersCounter $parametersCounter, bool $isAnd)
    {
        $this->column            = $column;
        $this->operator          = $operator;
        $this->value             = $value;
        $this->parametersCounter = $parametersCounter;
        $this->isAnd             = $isAnd;
    }


    protected function makeExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, string $parameterName): FilterExpression
    {

        $expr = $qb->expr()->comparison($column->getFullName($eavSettings), $this->operator, ':' . $parameterName);

        return new FilterExpression($expr, [ [ $parameterName, $this->value ] ], $this->column->getJoinTables($eavSettings), $this->isAnd);

    }
}