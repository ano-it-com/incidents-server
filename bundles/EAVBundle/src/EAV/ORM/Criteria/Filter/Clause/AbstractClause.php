<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractClause
{

    protected ColumnInterface $column;

    protected ParametersCounter $parametersCounter;


    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings): FilterExpression
    {
        $parameterName = $this->makePlaceholderParam();

        return $this->makeExpression($qb, $eavSettings, $this->column, $parameterName,);

    }


    protected function makePlaceholderParam(): string
    {
        return $this->parametersCounter->getNext();
    }


    abstract protected function makeExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, string $parameterName);

}