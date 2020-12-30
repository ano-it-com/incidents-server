<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelationType\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelationType\TypePropertyRelationTypeCriteriaInterface;

class TypePropertyRelationTypeFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, TypePropertyRelationTypeCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new TypePropertyRelationTypeFilterColumn($field);
    }
}