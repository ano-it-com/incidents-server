<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelationType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelationType\TypePropertyRelationTypeCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractWithNestedEntitiesPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelationType\Builder\EAVTypePropertyRelationTypeBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypePropertyRelationType\Builder\TypePropertyRelationTypeChangesCalculator;

class EAVTypePropertyRelationTypePersister extends AbstractWithNestedEntitiesPersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypePropertyRelationTypeBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        TypePropertyRelationTypeChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVTypePropertyRelationType::class;
    }


    protected function getFilterCriteriaInterface(): string
    {
        return TypePropertyRelationTypeCriteriaInterface::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY_RELATION_TYPE;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY_RELATION_TYPE_RESTRICTION;
    }


    protected function getNestedEntityForeignKey(): string
    {
        return 'type_property_relation_type_id';
    }


    protected function getNestedEntitiesKey(): string
    {
        return '_restrictions';
    }


    protected function beforeDeleteEntity(string $entityId): void
    {
        // проверка наличия связанных сущностей
        if ($this->hasRelationsWithThisType($entityId)) {
            throw new \RuntimeException('Cannot delete type property relation type, because there are relations with this type');
        }

        $restrictionsTableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY_RELATION_TYPE_RESTRICTION);

        // restrictions
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($restrictionsTableName)
                       ->where('type_property_relation_type_id = :id')
                       ->setParameter('id', $entityId);
        $qb->execute();
    }


    protected function hasRelationsWithThisType(string $entityId): bool
    {
        $relationTableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY_RELATION);
        $stmt              = $this->em->getConnection()
                                      ->createQueryBuilder()
                                      ->from($relationTableName)
                                      ->select('COUNT(*)')
                                      ->where($relationTableName . '.type_id = :id')
                                      ->setParameter('id', $entityId)
                                      ->setMaxResults(1)
                                      ->execute();

        $t = $stmt->fetchColumn();

        return (bool)$t;
    }

}