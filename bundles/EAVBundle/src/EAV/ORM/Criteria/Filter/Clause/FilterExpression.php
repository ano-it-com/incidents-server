<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class FilterExpression
{

    /** @var string|CompositeExpression */
    private $expression;

    private array $parameters;

    private bool $and;

    /** @var JoinTableParams[] */
    private array $joinTableParams;


    public function __construct($expression, array $parameters, array $joinTableParams, bool $and)
    {

        $this->expression      = $expression;
        $this->parameters      = $parameters;
        $this->joinTableParams = $joinTableParams;
        $this->and             = $and;
    }


    /**
     * @return CompositeExpression|string
     */
    public function getExpression()
    {
        return $this->expression;
    }


    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }


    /**
     * @return bool
     */
    public function isAnd(): bool
    {
        return $this->and;
    }


    /**
     * @return JoinTableParams[]
     */
    public function getJoinTableParams(): array
    {
        return $this->joinTableParams;
    }

}