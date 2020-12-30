<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

interface ClauseInterface
{

    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings): FilterExpression;
}