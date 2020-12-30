<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVEntityRelationTypeHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return EAVEntityRelationType::class;
    }


    public function getNestedEntityClass(): string
    {
        return EAVEntityRelationTypeRestriction::class;
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
            $data['entity_relation_type_id'] = $parentObject->getId();
        };
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [];
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'entity_relation_type_id' ];
    }
}