<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

interface ColumnInterface
{

    /**
     * @param EAVSettings $eavSettings
     *
     * @return JoinTableParams[]
     */
    public function getJoinTables(EAVSettings $eavSettings): array;


    public function getFullName(EAVSettings $eavSettings): string;
}