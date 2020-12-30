<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyFinder;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityPropertyValueByPropertyIdCriteria implements BasicFilterCriteriaClausesInterface, EntityFilterCriteriaInterface
{

    protected array $clausesRaw = [];

    protected ParametersCounter $parameterCounter;


    public function __construct()
    {
        $this->parameterCounter = new ParametersCounter();
    }


    public function where(string $propertyId, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'     => 'where',
            'propertyId' => $propertyId,
            'arguments'  => [ $operator, $value ]
        ];

        return $this;
    }


    public function orWhere(string $propertyId, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhere',
            'propertyId' => $propertyId,
            'arguments'  => [ $operator, $value ]
        ];

        return $this;
    }


    public function whereIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereIn',
            'propertyId' => $propertyId,
            'arguments'  => [ $values ]
        ];

        return $this;
    }


    public function orWhereIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereIn',
            'propertyId' => $propertyId,
            'arguments'  => [ $values ]
        ];

        return $this;
    }


    public function whereNotIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereNotIn',
            'propertyId' => $propertyId,
            'arguments'  => [ $values ]
        ];

        return $this;
    }


    public function orWhereNotIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereNotIn',
            'propertyId' => $propertyId,
            'arguments'  => [ $values ]
        ];

        return $this;
    }


    public function whereIsNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereIsNull',
            'propertyId' => $propertyId,
            'arguments'  => []
        ];

        return $this;
    }


    public function orWhereIsNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereIsNull',
            'propertyId' => $propertyId,
            'arguments'  => []
        ];

        return $this;
    }


    public function whereIsNotNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereIsNotNull',
            'propertyId' => $propertyId,
            'arguments'  => []
        ];

        return $this;
    }


    public function orWhereIsNotNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereIsNotNull',
            'propertyId' => $propertyId,
            'arguments'  => []
        ];

        return $this;
    }


    public function whereBetween(string $propertyId, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereBetween',
            'propertyId' => $propertyId,
            'arguments'  => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function orWhereBetween(string $propertyId, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereBetween',
            'propertyId' => $propertyId,
            'arguments'  => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function whereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'     => 'whereComposite',
            'propertyId' => null,
            'arguments'  => [ $innerCriteriaCallback ]
        ];

        return $this;
    }


    public function orWhereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'     => 'orWhereComposite',
            'propertyId' => null,
            'arguments'  => [ $innerCriteriaCallback ]
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
        $propertyIds = array_filter(array_values(array_unique(array_column($this->clausesRaw, 'propertyId'))), function ($alias) { return $alias !== null; });

        $propertyFinder = new PropertyFinder($qb->getConnection());

        $propertyVariantsForIds = $propertyFinder->getPropertyTypeVariantsByIds($propertyIds);

        $expressions = [];

        $deferredExpressionsMaker = new DeferredExpressionsMaker($qb, $this->parameterCounter, $eavSettings, self::class);

        foreach ($this->clausesRaw as $rawClause) {
            $propertyId = $rawClause['propertyId'];

            $propertyVariantsForId = $propertyVariantsForIds[$propertyId] ?? [];

            $expressions[] = $deferredExpressionsMaker->makeExpression($rawClause, $propertyId, $propertyVariantsForId);
        }

        return $expressions;

    }

}