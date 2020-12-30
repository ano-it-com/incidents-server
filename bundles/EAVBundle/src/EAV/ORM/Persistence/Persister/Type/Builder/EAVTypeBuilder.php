<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypeBuilder implements EAVTypeBuilderInterface
{

    protected EAVHydratorInterface $hydrator;


    public function __construct(EAVTypeHydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }


    public function buildEntities(array $typeRows, array $propertyRows): array
    {
        $combinedTypeRows = $this->combineRows($typeRows, $propertyRows);

        return $this->hydrator->hydrate($combinedTypeRows);
    }


    protected function combineRows(array $typeRows, array $propertyRows): array
    {
        $propertiesByType = [];

        foreach ($propertyRows as $propertyRow) {
            if ( ! isset($propertiesByType[$propertyRow['type_id']])) {
                $propertiesByType[$propertyRow['type_id']] = [];
            }

            $propertiesByType[$propertyRow['type_id']][] = $propertyRow;
        }

        foreach ($typeRows as &$typeRow) {
            $typeRow['_properties'] = $propertiesByType[$typeRow['id']] ?? [];
        }
        unset($typeRow);

        return $typeRows;
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }
}