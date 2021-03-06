<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelationType\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityRelationTypeFilterColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {

        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY_RELATION_TYPE) . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }
}