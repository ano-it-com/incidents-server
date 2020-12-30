<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityMetadataColumn implements ColumnInterface
{

    private string $columnName = 'meta';

    private string $fieldName;


    public function __construct(string $columnName)
    {
        $this->fieldName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY) . '.' . $this->columnName . '->>\'' . $this->fieldName . '\'';
    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }
}