<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\BasicMeta;

class BasicJsonMetaType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 10;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?BasicMeta
    {
        if ($value === null) {
            return null;
        }

        return new BasicMeta(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
    }


    public function convertToDatabase($value)
    {
        if ($value === null) {
            return null;
        }

        /** @var BasicMeta $value */
        return $value->toString();
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