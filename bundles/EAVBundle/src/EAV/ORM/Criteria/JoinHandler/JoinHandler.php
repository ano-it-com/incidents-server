<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler;

use Doctrine\DBAL\Query\QueryBuilder;

class JoinHandler
{

    public function joinTable(QueryBuilder $qb, JoinTableParams $joinTableParams): void
    {
        if ( ! $this->alreadyJoined($qb, $joinTableParams)) {
            $this->joinTableByParams($qb, $joinTableParams);
        }
    }


    private function alreadyJoined(QueryBuilder $qb, JoinTableParams $joinTableParams): bool
    {
        $joins = $qb->getQueryPart('join');

        $fromTable      = $joinTableParams->getFromTable();
        $joinTable      = $joinTableParams->getJoinTable();
        $joinTableAlias = $joinTableParams->getJoinTableAlias();

        if ( ! isset($joins[$fromTable])) {
            return false;
        }

        foreach ($joins[$fromTable] as $join) {
            if (
                $join['joinTable'] === $joinTable &&
                $join['joinAlias'] === $joinTableAlias &&
                $join['joinType'] === $joinTableParams->getJoinType() &&
                $join['joinCondition'] === $joinTableParams->getJoinCondition()

            ) {
                return true;
            }
        }

        return false;
    }


    private function joinTableByParams(QueryBuilder $qb, JoinTableParams $joinTableParams): void
    {
        $qb->add('join', [
            $joinTableParams->getFromTable() => [
                'joinType'      => $joinTableParams->getJoinType(),
                'joinTable'     => $joinTableParams->getJoinTable(),
                'joinAlias'     => $joinTableParams->getJoinTableAlias(),
                'joinCondition' => $joinTableParams->getJoinCondition(),
            ],
        ], true);
    }

}