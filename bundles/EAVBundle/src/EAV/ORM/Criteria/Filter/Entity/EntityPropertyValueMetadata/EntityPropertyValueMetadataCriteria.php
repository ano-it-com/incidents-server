<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValueMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereBetween;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereComposite;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNotNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereNotInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityPropertyValueMetadataCriteria implements BasicFilterCriteriaClausesInterface, EntityFilterCriteriaInterface
{

    protected array $clausesRaw = [];

    protected ParametersCounter $parameterCounter;


    public function __construct()
    {
        $this->parameterCounter = new ParametersCounter();
    }


    public function where(string $propertyAlias, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'    => 'where',
            'field'     => $propertyAlias,
            'arguments' => [ $operator, $value ]
        ];

        return $this;
    }


    public function orWhere(string $propertyAlias, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhere',
            'field'     => $propertyAlias,
            'arguments' => [ $operator, $value ]
        ];

        return $this;
    }


    public function whereIn(string $propertyAlias, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIn',
            'field'     => $propertyAlias,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function whereNotIn(string $propertyAlias, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereNotIn',
            'field'     => $propertyAlias,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function orWhereIn(string $propertyAlias, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIn',
            'field'     => $propertyAlias,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function orWhereNotIn(string $propertyAlias, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereNotIn',
            'field'     => $propertyAlias,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function whereIsNull(string $propertyAlias): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIsNull',
            'field'     => $propertyAlias,
            'arguments' => []
        ];

        return $this;
    }


    public function orWhereIsNull(string $propertyAlias): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIsNull',
            'field'     => $propertyAlias,
            'arguments' => []
        ];

        return $this;
    }


    public function whereIsNotNull(string $propertyAlias): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIsNotNull',
            'field'     => $propertyAlias,
            'arguments' => []
        ];

        return $this;
    }


    public function orWhereIsNotNull(string $propertyAlias): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIsNotNull',
            'field'     => $propertyAlias,
            'arguments' => []
        ];

        return $this;
    }


    public function whereBetween(string $propertyAlias, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereBetween',
            'field'     => $propertyAlias,
            'arguments' => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function orWhereBetween(string $propertyAlias, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereBetween',
            'field'     => $propertyAlias,
            'arguments' => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function whereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereComposite',
            'field'     => null,
            'arguments' => [ $innerCriteriaCallback ]
        ];

        return $this;
    }


    public function orWhereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereComposite',
            'field'     => null,
            'arguments' => [ $innerCriteriaCallback ]
        ];

        return $this;
    }


    /**
     * @param QueryBuilder                                                   $qb
     *
     * @param \ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings $eavSettings
     *
     * @return FilterExpression[]
     */
    public function getExpressions(QueryBuilder $qb, EAVSettings $eavSettings): array
    {
        $expressions = [];

        foreach ($this->clausesRaw as $rawClause) {
            $expressions[] = $this->makeExpression($qb, $rawClause, $eavSettings);
        }

        return $expressions;
    }


    private function makeExpression(QueryBuilder $qb, array $rawClause, EAVSettings $eavSettings): FilterExpression
    {
        $expressions = [];

        $methodString = $rawClause['method'];
        $field        = $rawClause['field'];

        [ $method, $isAnd ] = $this->analyzeMethod($methodString);

        //TODO - Refactor to value columns
        foreach ($eavSettings->getValueTablesNames() as $valueTablesName) {
            $valueColumn = $this->getColumn($field, $valueTablesName);

            $arguments = $rawClause['arguments'];
            array_unshift($arguments, $valueColumn);
            array_unshift($arguments, $eavSettings);
            array_unshift($arguments, $qb);
            $arguments[] = false;

            $valueExpression = $this->{$method}(...$arguments);

            $expressions[] = $valueExpression;
        }

        return $this->makeWhereCompositeClauseFromArray($qb, $eavSettings, $expressions, $isAnd);
    }


    private function analyzeMethod(string $methodString): array
    {
        $isAnd = true;

        if (strpos($methodString, 'or') === 0) {
            $methodString = substr($methodString, 2);
            $isAnd        = false;
        }

        return [ 'make' . ucfirst($methodString) . 'Expression', $isAnd ];
    }


    protected function getColumn(string $field, string $tableName): ColumnInterface
    {
        return new EntityPropertyValueMetadataColumn($field, $tableName);
    }


    /**
     * Создать композитное Выражение из массива Выражений
     *
     * @param QueryBuilder                                                   $qb
     * @param \ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings $eavSettings
     * @param array                                                          $expressions
     * @param                                                                $isAnd
     *
     * @return FilterExpression
     */
    protected function makeWhereCompositeClauseFromArray(QueryBuilder $qb, EAVSettings $eavSettings, array $expressions, $isAnd): FilterExpression
    {
        $joinParams = [];
        $parameters = [];

        $andExpressions = [];
        $orExpressions  = [];

        foreach ($expressions as $expression) {
            foreach ($expression->getJoinTableParams() as $joinTableParam) {
                $joinParams[] = $joinTableParam;
            }

            $parameters = [ ...$parameters, ...$expression->getParameters() ];

            if ($expression->isAnd()) {
                $andExpressions[] = $expression->getExpression();
            } else {
                $orExpressions[] = $expression->getExpression();
            }
        }

        $and = $qb->expr()->andX()->addMultiple($andExpressions);
        $or  = $qb->expr()->orX()->addMultiple($orExpressions);

        $composite = $qb->expr()->andX()->add($and)->add($or);

        return new FilterExpression($composite, $parameters, $joinParams, $isAnd);
    }


    private function makeWhereExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, string $operator, $value, bool $isAnd): FilterExpression
    {

        return (new WhereClause($column, $operator, $value, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereInExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereNotInExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereNotInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereIsNullExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereIsNullClause($column, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereIsNotNullExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereIsNotNullClause($column, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereBetweenExpression(QueryBuilder $qb, EAVSettings $eavSettings, ColumnInterface $column, $value1, $value2, bool $isAnd): FilterExpression
    {
        return (new WhereBetween($column, $value1, $value2, $this->parameterCounter, $isAnd))->getExpression($qb, $eavSettings);
    }


    private function makeWhereCompositeExpression(QueryBuilder $qb, EAVSettings $eavSettings, callable $innerCriteriaCallback, bool $isAnd): FilterExpression
    {
        /** @var FilterCriteriaInterface $freshCriteria */
        $freshCriteria = new static();

        $innerCriteriaCallback($freshCriteria);

        return (new WhereComposite($freshCriteria, $isAnd))->getExpression($qb, $eavSettings);
    }
}