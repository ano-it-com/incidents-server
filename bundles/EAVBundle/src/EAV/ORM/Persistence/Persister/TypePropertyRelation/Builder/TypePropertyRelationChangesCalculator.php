<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractSimpleChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class TypePropertyRelationChangesCalculator extends AbstractSimpleChangesCalculator implements ChangesCalculatorInterface
{

    protected function getEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY_RELATION;
    }
}

