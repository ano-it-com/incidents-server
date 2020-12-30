<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypeMetadataColumn implements ColumnInterface
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
        return $eavSettings->getTableNameForEntityType(EAVSettings::TYPE) . '.meta' . '->>\'' . $this->fieldName . '\'';
    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }
}