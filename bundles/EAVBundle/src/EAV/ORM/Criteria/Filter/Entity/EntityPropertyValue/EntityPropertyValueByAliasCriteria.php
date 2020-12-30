<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyFinder;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityPropertyValueByAliasCriteria implements BasicFilterCriteriaClausesInterface, EntityFilterCriteriaInterface
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


    public function orWhereIn(string $propertyAlias, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIn',
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
     * @param QueryBuilder $qb
     *
     * @param EAVSettings  $eavSettings
     *
     * @return FilterExpression[]
     */
    public function getExpressions(QueryBuilder $qb, EAVSettings $eavSettings): array
    {
        $propertyAliases = array_filter(array_values(array_unique(array_column($this->clausesRaw, 'field'))), function ($alias) { return $alias !== null; });

        $propertyFinder = new PropertyFinder($qb->getConnection());

        $propertyVariantsForAliases = $propertyFinder->getPropertyTypeVariantsByAliases($propertyAliases);

        $expressions = [];

        $deferredExpressionsMaker = new DeferredExpressionsMaker($qb, $this->parameterCounter, $eavSettings, self::class);

        foreach ($this->clausesRaw as $rawClause) {
            $field                    = $rawClause['field'];
            $propertyVariantsForAlias = $propertyVariantsForAliases[$field] ?? [];

            $expressions[] = $deferredExpressionsMaker->makeExpression($rawClause, $field, $propertyVariantsForAlias);
        }

        return $expressions;

    }

}