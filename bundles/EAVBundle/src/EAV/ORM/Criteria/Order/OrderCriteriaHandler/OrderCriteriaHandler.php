<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinHandler;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class OrderCriteriaHandler implements OrderCriteriaHandlerInterface
{

    private JoinHandler $joinHandler;


    public function __construct(JoinHandler $joinHandler)
    {
        $this->joinHandler = $joinHandler;
    }


    public function applyOrdering(QueryBuilder $qb, array $criteria, EAVSettings $EAVSettings): void
    {
        /** @var OrderCriteriaInterface $orderCriteria */
        foreach ($criteria as $index => $orderCriteria) {
            $expression = $orderCriteria->getExpression($qb, $EAVSettings, $index);

            foreach ($expression->getJoinTableParams() as $joinTable) {
                $this->joinHandler->joinTable($qb, $joinTable);
            }

            $sqlPart = $expression->getExpression() . ' ' . $expression->getDirection();

            if ($expression->getNullsPlace()) {
                $sqlPart .= ' NULLS ' . $expression->getNullsPlace();
            }

            $qb->add('orderBy', $sqlPart, true);
        }
    }
}