<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;

/**
 *
 * @method EAVEntityRelation[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVEntityRelation|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVEntityRelation|null find(string $id)
 *
 */
class EAVEntityRelationRepository extends EAVAbstractRepository implements EAVEntityRelationRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVEntityRelation::class;
    }

}