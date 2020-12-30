<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderExpression\OrderExpression;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

interface OrderCriteriaInterface
{

    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings, int $uniqueIndex): OrderExpression;
}