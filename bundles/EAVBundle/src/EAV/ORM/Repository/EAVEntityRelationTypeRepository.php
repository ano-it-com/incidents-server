<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;

/**
 *
 * @method EAVEntityRelationType[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVEntityRelationType|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVEntityRelationType|null find(string $id)
 *
 */
class EAVEntityRelationTypeRepository extends EAVAbstractRepository implements EAVEntityRelationTypeRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVEntityRelationType::class;
    }
}