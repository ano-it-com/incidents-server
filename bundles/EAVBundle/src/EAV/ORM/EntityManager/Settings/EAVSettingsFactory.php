<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;

class EAVSettingsFactory
{

    private array $config;

    private ValueTypes $valueTypes;


    public function __construct(array $config, ValueTypes $valueTypes)
    {
        $this->config     = $config;
        $this->valueTypes = $valueTypes;
    }


    public function make(): EAVSettings
    {
        $fieldValueTypeMapping = $this->makeFieldValueTypeMapping();
        $entitySettings        = $this->makeEntitySettings();

        return new EAVSettings($this->valueTypes, $fieldValueTypeMapping, $entitySettings);
    }


    private function makeFieldValueTypeMapping(): FieldValueTypeMapping
    {
        $mapping                  = [];
        $valueColumns             = [];
        $valueTypeToColumnMapping = [];

        $valueColumnsConfig = $this->config['base_tables'][EAVSettings::VALUES]['columns'] ?? [];
        foreach ($valueColumnsConfig as $columnName => $valueTypes) {
            if (stripos($columnName, 'value_') !== 0) {
                continue;
            }
            $valueColumns[] = $columnName;

            if ( ! is_array($valueTypes)) {
                $valueTypes = [ $valueTypes ];
            }

            foreach ($valueTypes as $valueTypeClass) {
                $valueType     = $this->valueTypes->getByClass($valueTypeClass);
                $valueTypeCode = $valueType->getCode();

                if (isset($valueTypeToColumnMapping[$valueTypeCode])) {
                    throw new \RuntimeException('Table for type ' . $valueTypeClass . ' is ambiguous - check config!');
                }

                $valueTypeToColumnMapping[$valueTypeCode] = $columnName;
            }
        }

        foreach ($this->config['base_tables'] as $baseCode => $settings) {
            $class = $settings['class'];

            $columns = $settings['columns'] ?? [];

            if ( ! count($columns)) {
                throw new \RuntimeException('Columns config not found in config for ' . $baseCode . ' found! Check bundle config file.');
            }

            foreach ($columns as $columnName => $typeClass) {
                if ($baseCode === 'values' && in_array($columnName, $valueColumns, true)) {
                    // value table skipping, because can be multiple and concrete type store in type property
                    continue;
                }
                $mapping[$class][$columnName] = $this->valueTypes->getByClass($typeClass);
            }
        }

        return new FieldValueTypeMapping($mapping, $valueColumns, $valueTypeToColumnMapping);
    }


    private function makeEntitySettings(): array
    {
        $entityCodes = [
            EAVSettings::ENTITY,
            EAVSettings::TYPE,
            EAVSettings::TYPE_PROPERTY,
            EAVSettings::VALUES,
            EAVSettings::ENTITY_RELATION,
            EAVSettings::ENTITY_RELATION_TYPE,
            EAVSettings::ENTITY_RELATION_TYPE_RESTRICTION,
            EAVSettings::TYPE_RELATION,
            EAVSettings::TYPE_RELATION_TYPE,
            EAVSettings::TYPE_RELATION_TYPE_RESTRICTION,
            EAVSettings::TYPE_PROPERTY_RELATION,
            EAVSettings::TYPE_PROPERTY_RELATION_TYPE,
            EAVSettings::TYPE_PROPERTY_RELATION_TYPE_RESTRICTION,
        ];

        $entitySettingsByCode = [];

        foreach ($entityCodes as $entityCode) {
            $config = $this->config['base_tables'][$entityCode] ?? [];
            if ( ! count($config)) {
                throw new \RuntimeException('No config for ' . $entityCode . ' found! Check bundle config file.');
            }

            $table = $config['table'] ?? null;
            if ( ! $table) {
                throw new \RuntimeException('Table name not found in config for ' . $entityCode . ' found! Check bundle config file.');
            }

            $entityClass = $config['class'] ?? null;
            if ( ! $entityClass) {
                throw new \RuntimeException('Entity class not found in config for ' . $entityCode . ' found! Check bundle config file.');
            }

            $entitySettingsByCode[$entityCode] = new EAVEntitySettings($table, $entityClass);
        }

        return $entitySettingsByCode;
    }

}