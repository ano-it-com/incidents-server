<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler;

class JoinTableParams
{

    protected string $fromTable;

    protected string $joinType;

    protected string $joinTable;

    protected ?string $joinTableAlias;

    protected string $joinCondition;


    public function __construct(
        string $fromTable,
        string $joinType,
        string $joinTable,
        ?string $joinTableAlias,
        string $joinCondition
    ) {
        $this->fromTable      = $fromTable;
        $this->joinType       = $joinType;
        $this->joinTable      = $joinTable;
        $this->joinTableAlias = $joinTableAlias;
        $this->joinCondition  = $joinCondition;
    }


    public function getFromTable(): string
    {
        return $this->fromTable;
    }


    public function getJoinType(): string
    {
        return $this->joinType;
    }


    public function getJoinTable(): string
    {
        return $this->joinTable;
    }


    public function getJoinTableAlias(): string
    {
        return $this->joinTableAlias ?: $this->joinTable;
    }


    public function getJoinCondition(): string
    {
        return $this->joinCondition;
    }

}