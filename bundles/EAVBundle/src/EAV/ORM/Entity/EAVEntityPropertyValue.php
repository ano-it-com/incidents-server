<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateType;

class EAVEntityPropertyValue
{

    protected string $id;

    protected $value;

    protected int $valueTypeCode;

    protected string $typePropertyId;

    protected $meta;


    public function __construct(string $id, EAVTypeProperty $typeProperty)
    {
        $this->id             = $id;
        $this->typePropertyId = $typeProperty->getId();
        $this->valueTypeCode  = $typeProperty->getValueType()->getCode();
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getValue()
    {
        return $this->value;
    }


    public function setValue($value): void
    {
        $this->value = $value;
    }


    public function getValueAsString(): ?string
    {
        if ( ! $this->value) {
            return $this->valueTypeCode;
        }
        if ($this->valueTypeCode === DateType::CODE) {
            return $this->value->format('Y-m-d');
        }
        if ($this->valueTypeCode === DateTimeType::CODE) {
            return $this->value->format('Y-m-d H:i:s');
        }

        if ($this->valueTypeCode === BasicJsonMetaType::CODE) {
            return $this->value->toString();
        }

        return $this->value;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getTypePropertyId(): string
    {
        return $this->typePropertyId;
    }


    public function getValueTypeCode(): int
    {
        return $this->valueTypeCode;
    }

}