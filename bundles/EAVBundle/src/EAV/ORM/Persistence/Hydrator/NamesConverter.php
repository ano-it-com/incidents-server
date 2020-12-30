<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator;

class NamesConverter
{

    public function snakeCaseToCamelCase(string $snakeCase): string
    {
        $camelCase    = str_replace('_', '', ucwords($snakeCase, '_'));
        $camelCase[0] = strtolower($camelCase[0]);

        return $camelCase;
    }


    public function camelCaseToSnakeCase(string $camelCase): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($camelCase)));
    }
}