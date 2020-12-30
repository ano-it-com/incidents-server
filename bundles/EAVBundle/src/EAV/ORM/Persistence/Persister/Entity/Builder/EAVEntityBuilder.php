<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVEntityBuilder implements EAVEntityBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVPersisterInterface $typePersister;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em            = $em;
        $this->hydrator      = $hydrator;
        $this->typePersister = $persistersFactory->getForClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $em);
    }


    public function buildEntities(array $entityRows, array $valuesRows): array
    {
        $typeIds = array_values(array_unique(array_column($entityRows, 'type_id')));

        /** @var EAVType[] $types */
        $types = $this->typePersister->loadByCriteria([ (new TypeFilterCriteria())->whereIn('id', $typeIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $valuesRows, $types);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $entityValues, array $types): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);

        $types = array_combine(array_map(static function (EAVType $type) { return $type->getId(); }, $types), $types);

        $typePropertyIdToValueTypeMapping = [];

        /** @var EAVType $type */
        foreach ($types as $type) {
            $properties = $type->getProperties();

            foreach ($properties as $property) {
                $typePropertyIdToValueTypeMapping[$property->getId()] = $property->getValueType()->getCode();
            }
        }

        foreach ($entityValues as $entityValue) {
            $valueType                  = $typePropertyIdToValueTypeMapping[$entityValue['type_property_id']];
            $entityValue['_value_type'] = $valueType;

            $columnName            = $this->em->getEavSettings()->getColumnNameForValueType($valueType);
            $entityValue['_value'] = $entityValue[$columnName];

            $entityRows[$entityValue['entity_id']]['_values'][] = $entityValue;
        }

        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_type'] = $types[$entityRow['type_id']];

            if ( ! isset($entityRows[$entityId]['_values'])) {
                $entityRows[$entityId]['_values'] = [];
            }

            $entityRows[$entityId]['_type'] = $types[$entityRow['type_id']];
        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}