<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder;

class PropertyInfo
{

    private string $id;

    private string $alias;

    private int $valueType;


    public function __construct(string $id, string $alias, int $valueType)
    {
        $this->id        = $id;
        $this->alias     = $alias;
        $this->valueType = $valueType;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getAlias(): string
    {
        return $this->alias;
    }


    public function getValueType(): int
    {
        return $this->valueType;
    }
}