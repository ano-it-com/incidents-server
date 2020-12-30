<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class DecimalType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 4;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?float
    {
        return $value === null ? null : (float)$value;
    }


    public function convertToDatabase($value)
    {
        return $value;
    }
}