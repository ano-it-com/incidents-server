<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

interface OrderCriteriaHandlerInterface
{

    public function applyOrdering(QueryBuilder $qb, array $criteria, EAVSettings $EAVSettings): void;
}