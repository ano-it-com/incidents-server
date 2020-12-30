<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypePropertyMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeFilterCriteriaInterface;

class TypePropertyMetadataFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface, TypeFilterCriteriaInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new TypePropertyMetadataColumn($field);
    }
}