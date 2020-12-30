<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVEntityRelation implements EAVPersistableInterface
{

    protected string $id;

    protected EAVEntity $from;

    protected EAVEntity $to;

    protected EAVEntityRelationType $type;

    protected $meta;


    public function __construct(string $id, EAVEntityRelationType $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getFrom(): EAVEntity
    {
        return $this->from;
    }


    public function setFrom(EAVEntity $from): void
    {
        $this->from = $from;
    }


    public function getTo(): EAVEntity
    {
        return $this->to;
    }


    public function setTo(EAVEntity $to): void
    {
        $this->to = $to;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getType(): EAVEntityRelationType
    {
        return $this->type;
    }

}