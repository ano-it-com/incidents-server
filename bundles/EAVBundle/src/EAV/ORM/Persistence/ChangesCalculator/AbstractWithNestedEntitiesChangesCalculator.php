<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

abstract class AbstractWithNestedEntitiesChangesCalculator
{

    protected EAVEntityManagerInterface $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function getChanges(array $newValues, array $oldValues): array
    {
        $changes       = [];
        $entityChanges = $this->getEntityChanges($newValues, $oldValues);
        if (count($entityChanges)) {
            $changes['entity'] = $entityChanges;
        }

        $nestedChanges = $this->getNestedChanges($newValues, $oldValues);
        if (count($nestedChanges)) {
            $changes['nested'] = $nestedChanges;
        }

        return $changes;
    }


    protected function getEntityChanges(array $newValues, array $oldValues): array
    {
        $nestedEntitiesKey = $this->getNestedEntitiesKey();
        unset($newValues[$nestedEntitiesKey], $oldValues[$nestedEntitiesKey]);

        $changes = [];

        foreach ($newValues as $key => $newValue) {
            if (array_key_exists($key, $oldValues)) {
                $oldValue = $oldValues[$key];

                if ($this->isEntityValueChanged($newValue, $oldValue, $key)) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            } else {
                $changes[$key] = [
                    'old' => null,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }


    abstract protected function getNestedEntitiesKey(): string;


    protected function isEntityValueChanged($newValue, $oldValue, $key): bool
    {
        $settings  = $this->em->getEavSettings();
        $valueType = $settings->getValueTypeForField($settings->getClassForEntityType($this->getEntityType()), $key);

        if ($valueType->isEqualDBValues($newValue, $oldValue)) {
            return false;
        }

        return true;
    }


    abstract protected function getEntityType(): string;


    protected function getNestedChanges(array $newValues, array $oldValues): array
    {
        $changes = [];

        $nestedEntitiesKey = $this->getNestedEntitiesKey();
        $newValues         = array_combine(array_column($newValues[$nestedEntitiesKey], 'id'), $newValues[$nestedEntitiesKey]);
        $oldValues         = array_combine(array_column($oldValues[$nestedEntitiesKey], 'id'), $oldValues[$nestedEntitiesKey]);

        foreach ($newValues as $valueId => $newValue) {
            if (array_key_exists($valueId, $oldValues)) {
                $oldValue = $oldValues[$valueId];

                if ($this->isNestedEntityValueChanged($newValue, $oldValue)) {
                    $changes['updated'][] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            } else {
                $changes['added'][] = [
                    'old' => null,
                    'new' => $newValue
                ];
            }
        }

        foreach ($oldValues as $valueId => $oldValue) {
            if ( ! array_key_exists($valueId, $newValues)) {
                $changes['removed'][] = [
                    'old' => $oldValue,
                    'new' => null
                ];
            }
        }

        return $changes;
    }


    protected function isNestedEntityValueChanged($newValue, $oldValue): bool
    {
        $settings = $this->em->getEavSettings();

        foreach ($newValue as $key => $newFieldValue) {
            if ( ! array_key_exists($key, $oldValue)) {
                return true;
            }

            $oldFieldValue = $oldValue[$key];

            $valueType = $settings->getValueTypeForField($settings->getClassForEntityType($this->getNestedEntityType()), $key);

            if ( ! $valueType->isEqualDBValues($newFieldValue, $oldFieldValue)) {
                return true;
            }

        }

        return false;
    }


    abstract protected function getNestedEntityType(): string;
}