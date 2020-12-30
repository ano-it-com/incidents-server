<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ValueTypeConverter;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

class ValueTypeConverter
{

    private EAVEntityManagerInterface $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function convertToPhpByClassField(string $class, string $field, $value)
    {
        $type = $this->em->getEavSettings()->getValueTypeForField($class, $field);

        return $type->convertToPhp($value);
    }


    public function convertToPhpByValueTypeCode(int $typeCode, $value)
    {
        $type = $this->em->getEavSettings()->getValueTypeByCode($typeCode);

        return $type->convertToPhp($value);

    }


    public function convertToDatabaseByClassField(string $class, string $field, $value)
    {
        $type = $this->em->getEavSettings()->getValueTypeForField($class, $field);

        return $type->convertToDatabase($value);
    }


    public function convertToDatabaseByValueTypeCode(int $typeCode, $value)
    {
        $type = $this->em->getEavSettings()->getValueTypeByCode($typeCode);

        return $type->convertToDatabase($value);

    }

}