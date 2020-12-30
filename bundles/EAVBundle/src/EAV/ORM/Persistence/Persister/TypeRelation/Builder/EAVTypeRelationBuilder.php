<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelationType\Type\TypeRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVTypeRelationBuilder implements EAVTypeRelationBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVPersisterInterface $relationTypePersister;

    protected EAVPersisterInterface $typePersister;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypeRelationHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                    = $em;
        $this->hydrator              = $hydrator;
        $this->relationTypePersister = $persistersFactory->getForClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE_RELATION_TYPE), $em);
        $this->typePersister         = $persistersFactory->getForClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $em);
    }


    public function buildEntities(array $entityRows): array
    {
        $relationTypeIds = array_values(array_unique(array_column($entityRows, 'type_id')));

        $typeFromIds = array_values(array_unique(array_column($entityRows, 'from_id')));
        $typeToIds   = array_values(array_unique(array_column($entityRows, 'to_id')));

        $typeIds = array_values(array_unique(array_merge($typeFromIds, $typeToIds)));

        $entities = $this->typePersister->loadByCriteria([ (new TypeFilterCriteria())->whereIn('id', $typeIds) ]);

        /** @var EAVTypeRelationType[] $relationTypes */
        $relationTypes = $this->relationTypePersister->loadByCriteria([ (new TypeRelationTypeFilterCriteria())->whereIn('id', $relationTypeIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $relationTypes, $entities);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $relationTypes, array $entities): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);

        $relationTypes = array_combine(array_map(static function (EAVTypeRelationType $type) { return $type->getId(); }, $relationTypes), $relationTypes);
        $entities      = array_combine(array_map(static function (EAVType $entity) { return $entity->getId(); }, $entities), $entities);

        //TODO - обработку ненайденных моделей
        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_type'] = $relationTypes[$entityRow['type_id']];
            $entityRows[$entityId]['_from'] = $entities[$entityRow['from_id']];
            $entityRows[$entityId]['_to']   = $entities[$entityRow['to_id']];
        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}