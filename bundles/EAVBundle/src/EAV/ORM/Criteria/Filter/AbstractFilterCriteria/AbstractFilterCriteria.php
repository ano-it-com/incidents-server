<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\ClauseInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereBetween;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereComposite;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNotNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereNotInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractFilterCriteria
{

    /** @var ClauseInterface[] */
    protected array $clauses = [];

    protected ParametersCounter $parameterCounter;


    public function __construct()
    {
        $this->parameterCounter = new ParametersCounter();
    }


    public function where(string $field, string $operator, $value): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_where($field, $operator, $value, $isAnd);
    }


    protected function _where(string $field, string $operator, $value, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereClause($column, $operator, $value, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    abstract protected function getColumn(string $field): ColumnInterface;


    public function orWhere(string $field, string $operator, $value): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_where($field, $operator, $value, $isAnd);
    }


    public function whereIn(string $field, array $values): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereIn($field, $values, $isAnd);
    }


    protected function _whereIn(string $field, array $values, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereInClause($column, $values, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereIn(string $field, array $values): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereIn($field, $values, $isAnd);
    }


    public function whereNotIn(string $field, array $values): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereNotIn($field, $values, $isAnd);
    }


    protected function _whereNotIn(string $field, array $values, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereNotInClause($column, $values, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereNotIn(string $field, array $values): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereNotIn($field, $values, $isAnd);
    }


    public function whereBetween(string $field, $value1, $value2): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereBetween($field, $value1, $value2, $isAnd);
    }


    protected function _whereBetween(string $field, $value1, $value2, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereBetween($column, $value1, $value2, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereBetween(string $field, $value1, $value2): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereBetween($field, $value1, $value2, $isAnd);
    }


    public function whereIsNull(string $field): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereIsNull($field, $isAnd);
    }


    protected function _whereIsNull(string $field, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereIsNullClause($column, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereIsNull(string $field): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereIsNull($field, $isAnd);
    }


    public function whereIsNotNull(string $field): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereIsNotNull($field, $isAnd);
    }


    protected function _whereIsNotNull(string $field, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        $column          = $this->getColumn($field);
        $this->clauses[] = new WhereIsNotNullClause($column, $this->parameterCounter, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereIsNotNull(string $field): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereIsNotNull($field, $isAnd);
    }


    public function whereComposite(callable $innerCriteriaCallback): BasicFilterCriteriaClausesInterface
    {
        $isAnd = true;

        return $this->_whereComposite($innerCriteriaCallback, $isAnd);
    }


    protected function _whereComposite(callable $innerCriteriaCallback, bool $isAnd): BasicFilterCriteriaClausesInterface
    {
        /** @var \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface $freshCriteria */
        $freshCriteria = new static();

        $innerCriteriaCallback($freshCriteria);

        $this->clauses[] = new WhereComposite($freshCriteria, $isAnd);

        /** @var BasicFilterCriteriaClausesInterface $this */
        return $this;
    }


    public function orWhereComposite(callable $innerCriteriaCallback): BasicFilterCriteriaClausesInterface
    {
        $isAnd = false;

        return $this->_whereComposite($innerCriteriaCallback, $isAnd);
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
        $expressions = [];
        foreach ($this->clauses as $clause) {
            $expressions[] = $clause->getExpression($qb, $eavSettings);
        }

        return $expressions;
    }
}