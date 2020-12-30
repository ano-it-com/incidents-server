<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class IntType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 3;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?int
    {
        return $value === null ? null : (int)$value;
    }


    public function convertToDatabase($value)
    {
        return $value;
    }
}