<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypeColumn implements \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {

        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTableNameForEntityType(EAVSettings::TYPE) . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }
}