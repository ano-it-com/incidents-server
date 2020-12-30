<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractWithNestedEntitiesChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class EntityRelationTypeChangesCalculator extends AbstractWithNestedEntitiesChangesCalculator implements ChangesCalculatorInterface
{

    protected function getNestedEntitiesKey(): string
    {
        return '_restrictions';
    }


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION_TYPE;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION_TYPE_RESTRICTION;
    }

}