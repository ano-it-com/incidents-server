<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractSimpleChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class EntityRelationChangesCalculator extends AbstractSimpleChangesCalculator implements ChangesCalculatorInterface
{

    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION;
    }
}