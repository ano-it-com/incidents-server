<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeFilterCriteriaInterface;

class TypeFilterCriteria extends AbstractFilterCriteria implements \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface, TypeFilterCriteriaInterface
{

    protected function getColumn(string $field): \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface
    {
        return new TypeColumn($field);
    }
}