<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelation\Relation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelation\TypePropertyRelationCriteriaInterface;

class TypePropertyRelationFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, TypePropertyRelationCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new TypePropertyRelationFilterColumn($field);
    }
}