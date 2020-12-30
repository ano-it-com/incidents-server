<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractWithNestedEntitiesChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class TypePropertyRelationTypeChangesCalculator extends AbstractWithNestedEntitiesChangesCalculator implements ChangesCalculatorInterface
{

    protected function getNestedEntitiesKey(): string
    {
        return '_restrictions';
    }


    protected function getEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY_RELATION_TYPE;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY_RELATION_TYPE_RESTRICTION;
    }

}