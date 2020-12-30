<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractWithNestedEntitiesChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class EntityChangesCalculator extends AbstractWithNestedEntitiesChangesCalculator implements ChangesCalculatorInterface
{

    protected function getNestedEntitiesKey(): string
    {
        return '_values';
    }


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY;
    }


    protected function isNestedEntityValueChanged($newValue, $oldValue): bool
    {
        $settings     = $this->em->getEavSettings();
        $valueColumns = $settings->getAllValueColumnsNames();

        foreach ($newValue as $key => $newFieldValue) {
            if ( ! array_key_exists($key, $oldValue)) {
                return true;
            }

            $oldFieldValue = $oldValue[$key];

            if ($key !== '_value' && strpos($key, '_') === 0) {
                // service values
                continue;
            }

            if (in_array($key, $valueColumns, true)) {
                // typed value columns
                continue;
            }

            if ($key === '_value') {
                $valueType = $settings->getValueTypeByCode($newValue['_value_type']);
            } else {
                $valueType = $settings->getValueTypeForField($settings->getClassForEntityType($this->getNestedEntityType()), $key);
            }

            if ( ! $valueType->isEqualDBValues($newFieldValue, $oldFieldValue)) {
                return true;
            }
        }

        return false;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::VALUES;
    }

}