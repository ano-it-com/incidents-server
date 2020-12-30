<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderExpression;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;

class OrderExpression
{

    private string $expression;

    private string $direction;

    /** @var JoinTableParams[] */
    private array $joinTableParams;

    private ?string $nullsPlace;


    public function __construct(string $expression, string $direction, ?string $nullsPlace, array $joinTableParams)
    {
        $this->expression = $expression;

        if ( ! in_array(strtolower($direction), [ 'asc', 'desc' ], true)) {
            throw new \InvalidArgumentException('Direction must be one of \'asc\', \'desc\'');
        }

        $this->direction       = $direction;
        $this->joinTableParams = $joinTableParams;

        if ($nullsPlace && ! in_array(strtolower($nullsPlace), [ 'first', 'last' ], true)) {
            throw new \InvalidArgumentException('NULLS place must be one of \'first\', \'last\'');
        }
        $this->nullsPlace = $nullsPlace;
    }


    public function getExpression(): string
    {
        return $this->expression;
    }


    public function getDirection(): string
    {
        return $this->direction;
    }


    public function getNullsPlace(): ?string
    {
        return $this->nullsPlace;
    }


    /**
     * @return JoinTableParams[]
     */
    public function getJoinTableParams(): array
    {
        return $this->joinTableParams;
    }
}