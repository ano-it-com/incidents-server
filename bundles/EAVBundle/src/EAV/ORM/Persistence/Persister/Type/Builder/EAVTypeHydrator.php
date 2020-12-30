<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypeHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return EAVType::class;
    }


    public function getNestedEntityClass(): string
    {
        return EAVTypeProperty::class;
    }


    protected function getDataFieldForNestedEntities(): string
    {
        return '_properties';
    }


    protected function getEntityFieldForNestedEntities(): string
    {
        return 'properties';
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'value_type' ];
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [];
    }


    protected function getNestedHydrationCallback(): ?callable
    {
        $eavSettings = $this->em->getEavSettings();

        return static function (object $entity, array $entityData) use ($eavSettings) {
            if (\array_key_exists('value_type', $entityData)) {
                $entity->valueType = $eavSettings->getValueTypeByCode($entityData['value_type']);
            }
        };
    }


    protected function getNestedExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object, object $parentObject) {
            $data['value_type'] = $object->valueType->getCode();
        };
    }

}