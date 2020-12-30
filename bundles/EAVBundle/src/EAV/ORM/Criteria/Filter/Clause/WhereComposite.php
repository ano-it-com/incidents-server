<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class WhereComposite implements ClauseInterface
{

    private FilterCriteriaInterface $criteria;

    private bool $isAnd;


    public function __construct(FilterCriteriaInterface $criteria, bool $isAnd)
    {
        $this->criteria = $criteria;
        $this->isAnd    = $isAnd;
    }


    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings): FilterExpression
    {
        $joinParams = [];
        $parameters = [];

        $andExpressions = [];
        $orExpressions  = [];

        foreach ($this->criteria->getExpressions($qb, $eavSettings) as $expression) {
            foreach ($expression->getJoinTableParams() as $joinTableParam) {
                $joinParams[] = $joinTableParam;
            }

            $parameters = [ ...$parameters, ...$expression->getParameters() ];

            if ($expression->isAnd()) {
                $andExpressions[] = $expression->getExpression();
            } else {
                $orExpressions[] = $expression->getExpression();
            }
        }

        $and = $qb->expr()->andX()->addMultiple($andExpressions);
        $or  = $qb->expr()->orX()->addMultiple($orExpressions);

        $composite = $qb->expr()->andX()->add($and)->add($or);

        return new FilterExpression($composite, $parameters, $joinParams, $this->isAnd);
    }
}