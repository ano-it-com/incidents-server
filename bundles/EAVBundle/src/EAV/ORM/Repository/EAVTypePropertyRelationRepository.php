<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelation;

/**
 *
 * @method EAVTypePropertyRelation[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVTypePropertyRelation|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVTypePropertyRelation|null find(string $id)
 *
 */
class EAVTypePropertyRelationRepository extends EAVAbstractRepository implements EAVTypePropertyRelationRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVTypePropertyRelation::class;
    }

}