<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use DateTime;
use DateTimeInterface;

class DateTimeType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 1;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?DateTimeInterface
    {
        if ($value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        $val = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ( ! $val) {
            throw new \RuntimeException('Can\'t convert to PHP DateTimeType value ' . $value);
        }

        return $val;
    }


    public function convertToDatabase($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        throw new \RuntimeException('Can\'t convert to DB DateTimeType value ' . $value);
    }
}