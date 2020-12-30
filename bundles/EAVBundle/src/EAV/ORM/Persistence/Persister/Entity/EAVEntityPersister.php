<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractWithNestedEntitiesPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EAVEntityBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EntityChangesCalculator;

class EAVEntityPersister extends AbstractWithNestedEntitiesPersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        EntityChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVEntity::class;
    }


    protected function getFilterCriteriaInterface(): string
    {
        return EntityFilterCriteriaInterface::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::VALUES;
    }


    protected function getNestedEntityForeignKey(): string
    {
        return 'entity_id';
    }


    protected function getNestedEntitiesKey(): string
    {
        return '_values';
    }


    protected function convertNestedEntityDataToDbData(array &$nestedData): void
    {
        unset($nestedData['_value_type'], $nestedData['_value']);
    }


    protected function beforeDeleteEntity(string $entityId): void
    {
        $valueTableName     = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::VALUES);
        $relationsTableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY_RELATION);

        // values
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($valueTableName)
                       ->where('entity_id = :id')
                       ->setParameter('id', $entityId);
        $qb->execute();

        // relations
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($relationsTableName)
                       ->orWhere('from_id = :id')
                       ->orWhere('to_id = :id')
                       ->setParameter('id', $entityId);
        $qb->execute();

    }

}