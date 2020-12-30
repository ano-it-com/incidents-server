<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter;

interface BasicFilterCriteriaClausesInterface extends FilterCriteriaInterface
{

    public function where(string $field, string $operator, $value): BasicFilterCriteriaClausesInterface;


    public function orWhere(string $field, string $operator, $value): BasicFilterCriteriaClausesInterface;


    public function whereIn(string $field, array $values): BasicFilterCriteriaClausesInterface;


    public function orWhereIn(string $field, array $values): BasicFilterCriteriaClausesInterface;


    public function whereComposite(callable $innerCriteriaCallback): BasicFilterCriteriaClausesInterface;


    public function orWhereComposite(callable $innerCriteriaCallback): BasicFilterCriteriaClausesInterface;
}