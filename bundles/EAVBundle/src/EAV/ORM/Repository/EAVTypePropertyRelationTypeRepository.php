<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;

/**
 *
 * @method EAVTypePropertyRelationType[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVTypePropertyRelationType|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVTypePropertyRelationType|null find(string $id)
 *
 */
class EAVTypePropertyRelationTypeRepository extends EAVAbstractRepository implements EAVTypePropertyRelationTypeRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVTypePropertyRelationType::class;
    }
}