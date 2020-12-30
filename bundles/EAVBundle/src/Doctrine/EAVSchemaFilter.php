<?php

namespace ANOITCOM\EAVBundle\Doctrine;

class EAVSchemaFilter
{

    public function __invoke($tableName): bool
    {
        return ! (is_string($tableName) && strpos($tableName, 'eav_') === 0);
    }
}