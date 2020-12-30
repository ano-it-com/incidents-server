<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypePropertyMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypePropertyMetadataColumn implements ColumnInterface
{

    private string $columnName;

    private string $fieldName;


    public function __construct(string $columnName)
    {
        $this->columnName = 'meta';
        $this->fieldName  = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY) . '.meta' . '->>\'' . $this->fieldName . '\'';
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
                $typePropertyTableName . '.type_id = ' . $typeTableName . '.id'
            ),
        ];
    }
}