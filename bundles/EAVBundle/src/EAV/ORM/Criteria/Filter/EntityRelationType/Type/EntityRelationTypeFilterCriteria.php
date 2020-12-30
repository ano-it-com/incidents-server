<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelationType\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelationType\EntityRelationTypeCriteriaInterface;

class EntityRelationTypeFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, EntityRelationTypeCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new EntityRelationTypeFilterColumn($field);
    }
}