<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class BoolType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 5;

    /** @var string[][] PostgreSQL booleans literals */
    private array $booleanLiterals = [
        'true'  => [
            't',
            'true',
            'y',
            'yes',
            'on',
            '1',
        ],
        'false' => [
            'f',
            'false',
            'n',
            'no',
            'off',
            '0',
        ],
    ];


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?bool
    {
        if (in_array(strtolower($value), $this->booleanLiterals['false'], true)) {
            return false;
        }

        return $value === null ? null : (bool)$value;

    }


    public function convertToDatabase($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || is_numeric($value)) {
            return (int)$value;
        }

        if ( ! is_string($value)) {
            return 1;
        }

        if (in_array(strtolower(trim($value)), $this->booleanLiterals['false'], true)) {
            return 0;
        }

        if (in_array(strtolower(trim($value)), $this->booleanLiterals['true'], true)) {
            return 1;
        }

        throw new \RuntimeException('Can\'t convert to DB BoolType value ' . $value);
    }


    public function isEqualDBValues($value1, $value2): bool
    {
        return (int)$value1 === (int)$value2;
    }
}