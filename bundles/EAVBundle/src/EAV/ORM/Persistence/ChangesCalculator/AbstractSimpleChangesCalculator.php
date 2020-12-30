<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

abstract class AbstractSimpleChangesCalculator
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

        return $changes;
    }


    protected function getEntityChanges(array $newValues, array $oldValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            if (array_key_exists($key, $oldValues)) {
                $oldValue = $oldValues[$key];

                if ($this->isChanged($newValue, $oldValue, $key)) {
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


    protected function isChanged($newValue, $oldValue, $key): bool
    {
        $settings  = $this->em->getEavSettings();
        $valueType = $settings->getValueTypeForField($settings->getClassForEntityType($this->getEntityType()), $key);

        if ($valueType->isEqualDBValues($newValue, $oldValue)) {
            return false;
        }

        return true;
    }


    abstract protected function getEntityType(): string;
}