<?php

namespace App\ReadModel\Loaders\Incident\Criteria;

use App\ReadModel\Loaders\CriteriaInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class ArraySortingCriteria implements CriteriaInterface
{

    private $sorting = [
        'id'            => 'incidents.id',
        'status_code'   => 'incident_status.code',
        'created_by_id' => 'incident.created_by_id',
        'date'          => 'incidents.date',
        'title'         => 'incidents.title',
        'coverage'      => 'incidents.coverage',
        'spread'        => 'incidents.spread',
        'importance'    => 'incidents.importance',
    ];

    /**
     * @var array
     */
    private $sortingParams;


    public function __construct(array $sortingParams)
    {

        $this->sortingParams = $sortingParams;
    }


    public function apply(QueryBuilder $queryBuilder): void
    {
        if (count($this->sortingParams) !== 2) {
            return;
        }
        $field  = $this->sortingParams[0];
        $column = $this->sorting[$field] ?? null;

        if ( ! $column) {
            return;
        }

        $dir = $this->sortingParams[1];
        if ( ! in_array(strtolower($dir), [ 'asc', 'desc' ], true)) {
            $dir = 'asc';
        }

        $queryBuilder->orderBy($column, $dir);

    }
}