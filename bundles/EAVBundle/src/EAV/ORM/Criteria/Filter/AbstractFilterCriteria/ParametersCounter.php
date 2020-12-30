<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria;

class ParametersCounter
{

    private int $counter = 0;

    private string $prefix;


    public function __construct()
    {
        $bytes        = random_bytes(5);
        $this->prefix = 'param_' . bin2hex($bytes);
    }


    public function getNext(): string
    {
        return $this->prefix . '_' . $this->counter++;
    }
}