<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinHandler;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class FilterCriteriaHandler implements CriteriaHandlerInterface
{

    private JoinHandler $joinHandler;


    public function __construct(JoinHandler $joinHandler)
    {

        $this->joinHandler = $joinHandler;
    }


    public function applyCriteria(QueryBuilder $qb, array $criteria, EAVSettings $eavSettings): void
    {
        foreach ($criteria as $oneCriteria) {
            if ( ! $oneCriteria instanceof FilterCriteriaInterface) {
                throw new \InvalidArgumentException('Each criteria must implements CriteriaInterface');
            }

            foreach ($oneCriteria->getExpressions($qb, $eavSettings) as $expression) {
                foreach ($expression->getJoinTableParams() as $joinTableParam) {
                    $this->joinHandler->joinTable($qb, $joinTableParam);
                }

                foreach ($expression->getParameters() as $oneParameters) {
                    $qb->setParameter(...$oneParameters);
                }

                if ($expression->isAnd()) {
                    $qb->andWhere($expression->getExpression());
                } else {
                    $qb->orWhere($expression->getExpression());
                }
            }

        }
    }
}