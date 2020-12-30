<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\Relation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\EntityRelationCriteriaInterface;

class EntityRelationFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, EntityRelationCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new EntityRelationFilterColumn($field);
    }
}