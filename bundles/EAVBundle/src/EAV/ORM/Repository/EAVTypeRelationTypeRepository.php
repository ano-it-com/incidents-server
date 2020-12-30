<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;

/**
 *
 * @method EAVTypeRelationType[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVTypeRelationType|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVTypeRelationType|null find(string $id)
 *
 */
class EAVTypeRelationTypeRepository extends EAVAbstractRepository implements EAVTypeRelationTypeRepositoryInterface
{

    public function getEntityClass(): string
    {
        return EAVTypeRelationType::class;
    }
}