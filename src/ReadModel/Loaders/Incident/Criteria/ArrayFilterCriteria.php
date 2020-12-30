<?php

namespace App\ReadModel\Loaders\Incident\Criteria;

use App\ReadModel\Loaders\CriteriaInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class ArrayFilterCriteria implements CriteriaInterface
{

    private $basicFilters = [
        'id' => [ 'column' => 'incidents.id' ],
        'created_by_id' => [ 'column' => 'incidents.created_by_id' ],
    ];

    private $dateFilters = [
        'date' => [ 'column' => 'incidents.date' ],
    ];

    private $multipleFilters = [
        'status_code' => [ 'column' => 'incident_statuses.code' ],
    ];

    private $likeFilters = [
        'title' => [ 'column' => 'incidents.title' ],
    ];

    /**
     * @var array
     */
    private $filters;


    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }


    public function apply(QueryBuilder $queryBuilder): void
    {
        $this->applyBasicFilters($queryBuilder);
        $this->applyDateFilter($queryBuilder);
        $this->applyLikeFilter($queryBuilder);
        $this->applyMultipleFilter($queryBuilder);
    }


    private function applyBasicFilters(QueryBuilder $qb): void
    {
        foreach ($this->filters as $field => $filterValue) {
            if ( ! array_key_exists($field, $this->basicFilters)) {
                continue;
            }
            $dbColumn = $this->basicFilters[$field]['column'];
            if (isset($this->basicFilters[$field]['joins']) && is_array($this->basicFilters[$field]['joins'])) {
                $this->joinColumns($qb, $this->basicFilters[$field]['joins']);
            }

            $qb->andWhere($dbColumn . ' = :' . $field)->setParameter($field, $filterValue);

        }

    }


    private function joinColumns(QueryBuilder $qb, array $joinDescriptions): void
    {
        foreach ($joinDescriptions as $joinDescription) {
            $qb->leftJoin(...$joinDescription);
        }
    }


    private function applyDateFilter(QueryBuilder $qb): void
    {
        foreach ($this->filters as $field => $date) {
            if ( ! array_key_exists($field, $this->dateFilters)) {
                continue;
            }
            $dbColumn = $this->dateFilters[$field]['column'];
            if (isset($this->dateFilters[$field]['joins']) && is_array($this->dateFilters[$field]['joins'])) {
                $this->joinColumns($qb, $this->dateFilters[$field]['joins']);
            }

            if ( ! $date || ! is_array($date)) {
                return;
            }

            if (count($date) !== 2) {
                return;
            }

            $dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d H:i:m', $date[0] . ' 00:00:00');
            $dateTo   = \DateTimeImmutable::createFromFormat('Y-m-d H:i:m', $date[1] . ' 23:59:59');

            if ( ! $dateFrom || ! $dateTo) {
                return;
            }

            $qb->andWhere($dbColumn . ' >= :' . $field . 'From')
               ->andWhere($dbColumn . ' <= :' . $field . 'To')
               ->setParameter($field . 'From', $dateFrom->format('Y-m-d H:i:m'))
               ->setParameter($field . 'To', $dateTo->format('Y-m-d H:i:m'));

        }


    }


    private function applyLikeFilter(QueryBuilder $qb): void
    {
        foreach ($this->filters as $field => $filterValue) {
            if ( ! $filterValue) {
                continue;
            }

            if ( ! array_key_exists($field, $this->likeFilters)) {
                continue;
            }

            $dbColumn = $this->likeFilters[$field]['column'];
            if (isset($this->likeFilters[$field]['joins']) && is_array($this->likeFilters[$field]['joins'])) {
                $this->joinColumns($qb, $this->likeFilters[$field]['joins']);
            }

            $filterValueToSearch = '%' . mb_strtolower($filterValue) . '%';

            $qb->andWhere('LOWER(' . $dbColumn . ') LIKE :' . $field)->setParameter($field, $filterValueToSearch);

        }


    }


    private function applyMultipleFilter(QueryBuilder $qb): void
    {

        foreach ($this->filters as $field => $filterValue) {
            if ( ! is_array($filterValue)) {
                continue;
            }
            if ( ! count($filterValue)) {
                continue;
            }

            if ( ! array_key_exists($field, $this->multipleFilters)) {
                continue;
            }

            $dbColumn = $this->multipleFilters[$field]['column'];
            if (isset($this->multipleFilters[$field]['joins']) && is_array($this->multipleFilters[$field]['joins'])) {
                $this->joinColumns($qb, $this->multipleFilters[$field]['joins']);
            }

            $orExpr = $qb->expr()->orX();
            foreach ($filterValue as $key => $value) {
                $orExpr->add($dbColumn . ' = :' . $field . '_' . $key);
                $qb->setParameter($field . '_' . $key, $value);
            }

            $qb->andWhere($orExpr);

        }

    }

}