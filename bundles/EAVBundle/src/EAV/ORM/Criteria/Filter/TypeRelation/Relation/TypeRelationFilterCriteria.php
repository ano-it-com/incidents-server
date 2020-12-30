<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelation\Relation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelation\TypeRelationCriteriaInterface;

class TypeRelationFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, TypeRelationCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new TypeRelationFilterColumn($field);
    }
}