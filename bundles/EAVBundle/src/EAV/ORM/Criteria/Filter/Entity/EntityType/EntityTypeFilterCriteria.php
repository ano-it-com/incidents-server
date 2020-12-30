<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface;

class EntityTypeFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, EntityFilterCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new EntityTypeColumn($field);
    }
}