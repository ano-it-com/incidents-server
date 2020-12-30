<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class FieldValueTypeMapping
{

    private array $mapping;

    private array $valueColumns;

    private array $valueTypeToColumnMapping;


    public function __construct(array $mapping, array $valueColumns, array $valueTypeToColumnMapping)
    {
        $this->mapping                  = $mapping;
        $this->valueColumns             = $valueColumns;
        $this->valueTypeToColumnMapping = $valueTypeToColumnMapping;
    }


    public function getValueTypeForField(string $class, string $field): ValueTypeInterface
    {
        if ( ! isset($this->mapping[$class][$field])) {
            throw new \InvalidArgumentException('Class ' . $class . ' or field ' . $field . ' not found int types mapping. Check bundle settings.');
        }

        return $this->mapping[$class][$field];

    }


    public function getMappingForEntityClass(string $entityClass): array
    {
        return $this->mapping[$entityClass] ?? [];
    }


    public function getAllValueColumnsNames(): array
    {
        return $this->valueColumns;
    }


    public function getColumnNameForValueType(int $valueType): string
    {
        if ( ! isset($this->valueTypeToColumnMapping[$valueType])) {
            throw new \RuntimeException('Table for type ' . $valueType . ' not found!');
        }

        return $this->valueTypeToColumnMapping[$valueType];
    }
}