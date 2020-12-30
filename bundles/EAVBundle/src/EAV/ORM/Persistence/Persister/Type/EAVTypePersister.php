<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractWithNestedEntitiesPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder\EAVTypeBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder\TypeChangesCalculator;

class EAVTypePersister extends AbstractWithNestedEntitiesPersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypeBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        TypeChangesCalculator $changesCalculator
    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->eavSettings          = $this->em->getEavSettings();
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVType::class;
    }


    protected function getFilterCriteriaInterface(): string
    {
        return TypeFilterCriteriaInterface::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::TYPE;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::TYPE_PROPERTY;
    }


    protected function getNestedEntityForeignKey(): string
    {
        return 'type_id';
    }


    protected function getNestedEntitiesKey(): string
    {
        return '_properties';
    }


    protected function beforeDeleteEntity(string $entityId): void
    {
        $propertyTableName  = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY);
        $relationsTableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::TYPE_RELATION);

        // values
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($propertyTableName)
                       ->where('type_id = :id')
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