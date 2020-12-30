<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereBetween;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereComposite;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNotNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereNotInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyInfo;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class DeferredExpressionsMaker
{

    private QueryBuilder $qb;

    private ParametersCounter $parameterCounter;

    private EAVSettings $eavSettings;

    private string $callerClass;


    public function __construct(
        QueryBuilder $qb,
        ParametersCounter $parameterCounter,
        EAVSettings $eavSettings,
        string $callerClass
    ) {
        $this->qb               = $qb;
        $this->parameterCounter = $parameterCounter;
        $this->eavSettings      = $eavSettings;
        $this->callerClass      = $callerClass;
    }


    /**
     * @param array          $rawClause
     * @param string|null    $field
     * @param PropertyInfo[] $propertyVariantsForField
     *
     * @return FilterExpression
     */
    public function makeExpression(array $rawClause, ?string $field, array $propertyVariantsForField): FilterExpression
    {
        $methodString = $rawClause['method'];

        [ $method, $isAnd ] = $this->analyzeMethod($methodString);

        if ( ! $field) {
            $arguments   = $rawClause['arguments'];
            $arguments[] = $isAnd;

            return $this->{$method}(...$arguments);
        }

        if ( ! count($propertyVariantsForField)) {
            return $this->makeNeverTrueExpression($isAnd);
        }

        $expressions = [];

        foreach ($propertyVariantsForField as $propertyInfo) {
            $valueColumn        = new EntityPropertyValueColumn($this->eavSettings->getColumnNameForValueType($propertyInfo->getValueType()));
            $propertyTypeColumn = new EntityPropertyValueColumn('type_property_id');

            if ( ! method_exists($this, $method)) {
                throw new \InvalidArgumentException('Method ' . $method . ' not found');
            }

            $arguments = $rawClause['arguments'];
            array_unshift($arguments, $valueColumn);
            $arguments[] = true;

            $valueExpression        = $this->{$method}(...$arguments);
            $propertyTypeExpression = $this->makeWhereExpression($propertyTypeColumn, '=', $propertyInfo->getId(), true);

            $expressions[] = $this->makeWhereCompositeClauseFromArray([ $valueExpression, $propertyTypeExpression ], false);
        }

        return $this->makeWhereCompositeClauseFromArray($expressions, $isAnd);
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


    private function makeNeverTrueExpression(bool $isAnd): FilterExpression
    {
        return new FilterExpression('false', [], [], $isAnd);
    }


    private function makeWhereExpression(ColumnInterface $column, string $operator, $value, bool $isAnd): FilterExpression
    {

        return (new WhereClause($column, $operator, $value, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    protected function makeWhereCompositeClauseFromArray(array $expressions, $isAnd): FilterExpression
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

        $and = $this->qb->expr()->andX()->addMultiple($andExpressions);
        $or  = $this->qb->expr()->orX()->addMultiple($orExpressions);

        $composite = $this->qb->expr()->andX()->add($and)->add($or);

        return new FilterExpression($composite, $parameters, $joinParams, $isAnd);
    }


    private function makeWhereInExpression(ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    private function makeWhereNotInExpression(ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereNotInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    private function makeWhereIsNullExpression(ColumnInterface $column, bool $isAnd): FilterExpression
    {
        return (new WhereIsNullClause($column, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    private function makeWhereIsNotNullExpression(ColumnInterface $column, bool $isAnd): FilterExpression
    {
        return (new WhereIsNotNullClause($column, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    private function makeWhereBetweenExpression(ColumnInterface $column, $value1, $value2, bool $isAnd): FilterExpression
    {
        return (new WhereBetween($column, $value1, $value2, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    private function makeWhereCompositeExpression(callable $innerCriteriaCallback, bool $isAnd): FilterExpression
    {
        /** @var \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface $freshCriteria */
        $freshCriteria = new $this->callerClass();

        $innerCriteriaCallback($freshCriteria);

        return (new WhereComposite($freshCriteria, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }

}