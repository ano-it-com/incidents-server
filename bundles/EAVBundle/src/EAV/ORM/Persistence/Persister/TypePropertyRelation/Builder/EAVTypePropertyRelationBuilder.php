<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelationType\Type\TypePropertyRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class EAVTypePropertyRelationBuilder implements EAVTypePropertyRelationBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVPersisterInterface $relationTypePersister;

    protected EAVPersisterInterface $typePersister;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypePropertyRelationHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                    = $em;
        $this->hydrator              = $hydrator;
        $this->relationTypePersister = $persistersFactory->getForClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE_PROPERTY_RELATION_TYPE), $em);
        $this->typePersister         = $persistersFactory->getForClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $em);
    }


    public function buildEntities(array $entityRows): array
    {
        $relationTypeIds = array_values(array_unique(array_column($entityRows, 'type_id')));

        $propertiesFromIds = array_values(array_unique(array_column($entityRows, 'from_id')));
        $propertiesToIds   = array_values(array_unique(array_column($entityRows, 'to_id')));

        $propertiesIds = array_values(array_unique(array_merge($propertiesFromIds, $propertiesToIds)));

        $typeIds = $this->loadTypeIdsForProperties($propertiesIds);

        $types = $this->typePersister->loadByCriteria([ (new TypeFilterCriteria())->whereIn('id', $typeIds) ]);

        $propertiesById = $this->getPropertiesFromTypes($types, $propertiesIds);

        /** @var EAVTypePropertyRelationType[] $relationTypes */
        $relationTypes = $this->relationTypePersister->loadByCriteria([ (new TypePropertyRelationTypeFilterCriteria())->whereIn('id', $relationTypeIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $relationTypes, $propertiesById);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function loadTypeIdsForProperties(array $propertiesIds): array
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY);

        $stmt = $this->em->getConnection()
                         ->createQueryBuilder()
                         ->from($tableName)
                         ->select([ $tableName . '.type_id' ])
                         ->where($tableName . '.id in (:ids)')
                         ->setParameter('ids', $propertiesIds, Connection::PARAM_STR_ARRAY)
                         ->distinct()
                         ->execute();

        $data = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        return array_column($data, 'type_id');
    }


    protected function getPropertiesFromTypes(array $types, array $propertiesIds): array
    {
        $propertiesById = [];
        /** @var EAVType $type */
        foreach ($types as $type) {
            foreach ($type->getProperties() as $property) {
                $propertyId = $property->getId();
                if (in_array($propertyId, $propertiesIds, true)) {
                    $propertiesById[$propertyId] = $property;
                }
            }
        }

        return $propertiesById;
    }


    protected function combineRows(array $entityRows, array $relationTypes, array $propertiesById): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);

        $relationTypes = array_combine(array_map(static function (EAVTypePropertyRelationType $type) { return $type->getId(); }, $relationTypes), $relationTypes);

        //TODO - обработку ненайденных моделей
        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_type'] = $relationTypes[$entityRow['type_id']];
            $entityRows[$entityId]['_from'] = $propertiesById[$entityRow['from_id']];
            $entityRows[$entityId]['_to']   = $propertiesById[$entityRow['to_id']];
        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}