<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class JsonType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 20;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?array
    {
        if ($value === null) {
            return null;
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }


    public function convertToDatabase($value)
    {
        if ($value === null) {
            return null;
        }

        /** @var array $value */
        return json_encode($value, JSON_THROW_ON_ERROR);
    }


    public function isEqualDBValues($value1, $value2): bool
    {
        if ($value1 === null || $value2 === null) {
            return $value1 === $value2;
        }

        $array1 = json_decode($value1, true, 512, JSON_THROW_ON_ERROR);
        $array2 = json_decode($value2, true, 512, JSON_THROW_ON_ERROR);

        // not strict comparison because order doesn't mean
        return $array1 == $array2;
    }

}