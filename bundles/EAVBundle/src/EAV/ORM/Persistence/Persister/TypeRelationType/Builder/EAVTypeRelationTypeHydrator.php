<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypeRelationTypeHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return EAVTypeRelationType::class;
    }


    public function getNestedEntityClass(): string
    {
        return EAVTypeRelationTypeRestriction::class;
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
            $data['type_relation_type_id'] = $parentObject->getId();
        };
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [];
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'type_relation_type_id' ];
    }

}