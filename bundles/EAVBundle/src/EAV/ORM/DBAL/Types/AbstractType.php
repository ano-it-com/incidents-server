<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

abstract class AbstractType
{

    public function isEqualDBValues($value1, $value2): bool
    {
        return $value1 === $value2;
    }

}