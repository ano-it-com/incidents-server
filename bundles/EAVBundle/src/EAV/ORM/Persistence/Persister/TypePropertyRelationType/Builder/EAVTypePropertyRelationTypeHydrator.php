<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypePropertyRelationTypeHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return EAVTypePropertyRelationType::class;
    }


    public function getNestedEntityClass(): string
    {
        return EAVTypePropertyRelationTypeRestriction::class;
    }


    protected function getDataFieldForNestedEntities(): string
    {
        return '_restrictions';
    }


    protected function getEntityFieldForNestedEntities(): string
    {
        return 'restrictions';
    }


    protected function getNestedExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object, object $parentObject) {
            $data['type_property_relation_type_id'] = $parentObject->getId();
        };
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [];
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'type_property_relation_type_id' ];
    }

}