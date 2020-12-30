<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\EntityRelationCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractSimplePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\Builder\EAVEntityRelationBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\Builder\EntityRelationChangesCalculator;

class EAVEntityRelationPersister extends AbstractSimplePersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityRelationBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        EntityRelationChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVEntityRelation::class;
    }


    protected function getFilterCriteriaInterface(): string
    {
        return EntityRelationCriteriaInterface::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION;
    }

}