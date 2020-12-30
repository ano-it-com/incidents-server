<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVTypeRelationType implements EAVPersistableInterface
{

    protected string $id;

    protected string $alias;

    protected string $title;

    protected $meta;

    /**
     * @var EAVTypeRelationTypeRestriction[]
     */
    protected array $restrictions = [];


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
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


    /**
     * @return EAVTypeRelationTypeRestriction[]
     */
    public function getRestrictions(): array
    {
        return $this->restrictions;
    }


    /**
     * @param EAVTypeRelationTypeRestriction[] $restrictions
     */
    public function setRestrictions(array $restrictions): void
    {
        $this->restrictions = $restrictions;
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