<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelation\TypeRelationCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelation;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractSimplePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelation\Builder\EAVTypeRelationBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\TypeRelation\Builder\TypeRelationChangesCalculator;

class EAVTypeRelationPersister extends AbstractSimplePersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypeRelationBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        TypeRelationChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVTypeRelation::class;
    }


    protected function getFilterCriteriaInterface(): string
    {
        return TypeRelationCriteriaInterface::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::TYPE_RELATION;
    }

}