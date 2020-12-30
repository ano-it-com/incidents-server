<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValueMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityPropertyValueMetadataColumn implements ColumnInterface
{

    private string $tableName;

    private string $columnName;

    private string $fieldName;


    public function __construct(string $fieldName, $tableName)
    {
        $this->tableName  = $tableName;
        $this->columnName = 'meta';
        $this->fieldName  = $fieldName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $this->tableName . '.' . $this->columnName . '->>\'' . $this->fieldName . '\'';
    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $entityTableName = $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY);

        return [
            new JoinTableParams($entityTableName,
                'left',
                $this->tableName,
                null,
                $entityTableName . '.id = ' . $this->tableName . '.entity_id'),
        ];
    }
}