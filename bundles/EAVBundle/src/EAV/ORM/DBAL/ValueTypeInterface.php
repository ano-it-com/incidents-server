<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL;

interface ValueTypeInterface
{

    public function getCode(): int;


    public function convertToPhp($value);


    public function convertToDatabase($value);


    public function isEqualDBValues($value1, $value2): bool;

}