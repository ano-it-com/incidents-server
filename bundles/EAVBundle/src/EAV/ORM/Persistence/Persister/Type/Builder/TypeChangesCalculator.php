<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractWithNestedEntitiesChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class TypeChangesCalculator extends AbstractWithNestedEntitiesChangesCalculator implements ChangesCalculatorInterface
{

    protected function getNestedEntitiesKey(): string
    {
        return '_properties';
    }


    protected function getEntityType(): string
    {
        return EAVSettings::TYPE;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY;
    }

}