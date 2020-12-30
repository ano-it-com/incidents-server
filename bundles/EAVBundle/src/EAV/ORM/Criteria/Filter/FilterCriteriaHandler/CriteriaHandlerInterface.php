<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

interface CriteriaHandlerInterface
{

    public function applyCriteria(QueryBuilder $qb, array $criteria, EAVSettings $eavSettings): void;
}