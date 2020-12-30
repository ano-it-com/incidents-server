<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVTypePropertyRelation implements EAVPersistableInterface
{

    protected string $id;

    protected EAVTypeProperty $from;

    protected EAVTypeProperty $to;

    protected EAVTypePropertyRelationType $type;

    protected $meta;


    public function __construct(string $id, EAVTypePropertyRelationType $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getFrom(): EAVTypeProperty
    {
        return $this->from;
    }


    public function setFrom(EAVTypeProperty $from): void
    {
        $this->from = $from;
    }


    public function getTo(): EAVTypeProperty
    {
        return $this->to;
    }


    public function setTo(EAVTypeProperty $to): void
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


    public function getType(): EAVTypePropertyRelationType
    {
        return $this->type;
    }

}