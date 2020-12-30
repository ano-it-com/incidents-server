<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class EAVTypeProperty
{

    protected string $id;

    protected string $typeId;

    protected ValueTypeInterface $valueType;

    protected string $alias;

    protected string $title;

    protected $meta;


    public function __construct(string $id, EAVType $type, ValueTypeInterface $valueType)
    {
        $this->id        = $id;
        $this->valueType = $valueType;
        $this->typeId    = $type->getId();
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getTypeId(): string
    {
        return $this->typeId;
    }


    public function getAlias(): string
    {
        return $this->alias;
    }


    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getValueType(): ValueTypeInterface
    {
        return $this->valueType;
    }

}