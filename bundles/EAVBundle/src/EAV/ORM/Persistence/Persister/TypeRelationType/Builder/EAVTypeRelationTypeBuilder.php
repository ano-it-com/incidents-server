<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypeRelationTypeBuilder implements EAVTypeRelationTypeBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypeRelationTypeHydrator $hydrator
    ) {
        $this->em       = $em;
        $this->hydrator = $hydrator;
    }


    public function buildEntities(array $entityRows, array $restrictionRows): array
    {
        $combinedEntityRows = $this->combineRows($entityRows, $restrictionRows);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $restrictionRows): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);

        $restrictionRowsByEntity = [];

        foreach ($restrictionRows as $restrictionRow) {
            $restrictionRowsByEntity[$restrictionRow['type_relation_type_id']][] = $restrictionRow;
        }

        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_restrictions'] = $restrictionRowsByEntity[$entityRow['id']] ?? [];
        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}