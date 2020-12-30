<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelation;

/**
 *
 * @method EAVTypeRelation[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVTypeRelation|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVTypeRelation|null find(string $id)
 *
 */
class EAVTypeRelationRepository extends EAVAbstractRepository implements EAVTypeRelationRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVTypeRelation::class;
    }

}