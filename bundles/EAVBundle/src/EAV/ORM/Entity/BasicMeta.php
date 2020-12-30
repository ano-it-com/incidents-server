<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class BasicMeta
{

    private array $meta;


    public function __construct(array $meta)
    {
        $this->meta = $meta;
    }


    public function toString(): ?string
    {
        $json = json_encode($this->meta, JSON_THROW_ON_ERROR);
        if ( ! $json) {
            return null;
        }

        return $json;
    }
}