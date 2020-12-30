<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeProperty;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypePropertyColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {
        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY) . '.' . $this->columnName;
    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $typeTableName         = $eavSettings->getTableNameForEntityType(EAVSettings::TYPE);
        $typePropertyTableName = $eavSettings->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY);

        return [
            new JoinTableParams(
                $typeTableName,
                'left',
                $typePropertyTableName,
                null,
                $typeTableName . '.id =' . $typePropertyTableName . '.type_id'
            ),
        ];
    }
}