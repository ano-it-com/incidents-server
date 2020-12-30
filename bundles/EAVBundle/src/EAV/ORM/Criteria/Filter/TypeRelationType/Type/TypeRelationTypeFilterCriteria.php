<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelationType\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelationType\TypeRelationTypeCriteriaInterface;

class TypeRelationTypeFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, TypeRelationTypeCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new TypeRelationTypeFilterColumn($field);
    }
}