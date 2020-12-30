<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVTypeRelation implements EAVPersistableInterface
{

    protected string $id;

    protected EAVType $from;

    protected EAVType $to;

    protected EAVTypeRelationType $type;

    protected $meta;


    public function __construct(string $id, EAVTypeRelationType $type)
    {
        $this->id   = $id;
        $this->type = $type;

    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getFrom(): EAVType
    {
        return $this->from;
    }


    public function setFrom(EAVType $from): void
    {
        $this->from = $from;
    }


    public function getTo(): EAVType
    {
        return $this->to;
    }


    public function setTo(EAVType $to): void
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


    public function getType(): EAVTypeRelationType
    {
        return $this->type;
    }

}