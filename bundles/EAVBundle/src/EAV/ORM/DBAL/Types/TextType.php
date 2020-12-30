<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class TextType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 0;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?string
    {
        return is_resource($value) ? stream_get_contents($value) : $value;
    }


    public function convertToDatabase($value)
    {
        return $value;
    }
}