<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVTypePropertyRelationTypeRestriction
{

    protected string $id;

    protected string $restrictionTypeCode;

    protected array $restriction;

    protected $meta;


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getRestrictionTypeCode(): string
    {
        return $this->restrictionTypeCode;
    }


    public function setRestrictionTypeCode(string $restrictionTypeCode): void
    {
        $this->restrictionTypeCode = $restrictionTypeCode;
    }


    public function getRestriction(): array
    {
        return $this->restriction;
    }


    public function setRestriction(array $restriction): void
    {
        $this->restriction = $restriction;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }
}