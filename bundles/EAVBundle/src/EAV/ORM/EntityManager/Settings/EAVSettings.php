<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;

class EAVSettings
{

    public const ENTITY = 'entity';
    public const TYPE = 'type';
    public const TYPE_PROPERTY = 'type_property';
    public const VALUES = 'values';
    public const ENTITY_RELATION = 'entity_relation';
    public const ENTITY_RELATION_TYPE = 'entity_relation_type';
    public const ENTITY_RELATION_TYPE_RESTRICTION = 'entity_relation_type_restriction';
    public const TYPE_RELATION = 'type_relation';
    public const TYPE_RELATION_TYPE = 'type_relation_type';
    public const TYPE_RELATION_TYPE_RESTRICTION = 'type_relation_type_restriction';
    public const TYPE_PROPERTY_RELATION = 'type_property_relation';
    public const TYPE_PROPERTY_RELATION_TYPE = 'type_property_relation_type';
    public const TYPE_PROPERTY_RELATION_TYPE_RESTRICTION = 'type_property_relation_type_restriction';

    private FieldValueTypeMapping $fieldValueTypeMapping;

    private array $entitySettings;

    private ValueTypes $valueTypes;


    public function __construct(ValueTypes $valueTypes, FieldValueTypeMapping $fieldValueTypeMapping, array $entitySettings)
    {

        $this->fieldValueTypeMapping = $fieldValueTypeMapping;
        $this->entitySettings        = $entitySettings;
        $this->valueTypes            = $valueTypes;
    }


    public function getValueTypeByCode(int $valueTypeCode): ValueTypeInterface
    {
        return $this->valueTypes->getByCode($valueTypeCode);
    }


    public function getClassForEntityType(string $entityType): string
    {
        return $this->getEntitySettings($entityType)->getEntityClass();
    }


    private function getEntitySettings(string $entityType): EAVEntitySettings
    {
        $entitySettings = $this->entitySettings[$entityType] ?? null;

        if ($entitySettings === null) {
            throw new \RuntimeException('Entity settings for entity type' . $entityType . ' not found in config');
        }

        return $entitySettings;
    }


    public function getTableNameForEntityType(string $entityType): string
    {
        return $this->getEntitySettings($entityType)->getTableName();
    }


    public function getColumnNameForValueType(int $valueType): string
    {
        return $this->fieldValueTypeMapping->getColumnNameForValueType($valueType);
    }


    public function getAllValueColumnsNames(): array
    {
        return $this->fieldValueTypeMapping->getAllValueColumnsNames();
    }


    public function getValueTypeForField(string $class, string $field): ValueTypeInterface
    {
        return $this->fieldValueTypeMapping->getValueTypeForField($class, $field);

    }


    public function getFieldsMappingForEntityClass(string $class): array
    {
        return $this->fieldValueTypeMapping->getMappingForEntityClass($class);
    }

}