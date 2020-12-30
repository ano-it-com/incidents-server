<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractSimpleHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypePropertyRelationHydrator extends AbstractSimpleHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return EAVTypePropertyRelation::class;
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_type'], $data['_from'], $data['_to']);

        return $data;
    }


    protected function getHydrationCallback(): ?callable
    {
        return static function (object $object, array $data) {
            $object->from = $data['_from'];
            $object->to   = $data['_to'];
            $object->type = $data['_type'];
        };
    }


    protected function getExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object) {
            $data['from_id'] = $object->getFrom()->getId();
            $data['to_id']   = $object->getTo()->getId();
            $data['type_id'] = $object->getType()->getId();
        };
    }


    protected function getDbExcludeFields(): array
    {
        return [
            'type_id',
            'from_id',
            'to_id',
        ];
    }

}