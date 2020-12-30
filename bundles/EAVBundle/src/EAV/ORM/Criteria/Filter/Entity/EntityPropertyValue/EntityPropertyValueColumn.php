<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityPropertyValueColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {
        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::VALUES) . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $entityTableName = $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY);
        $valuesTableName = $eavSettings->getTableNameForEntityType(EAVSettings::VALUES);

        return [
            new JoinTableParams($entityTableName, 'left', $valuesTableName, null, $entityTableName . '.id = ' . $valuesTableName . '.entity_id'),
        ];
    }
}