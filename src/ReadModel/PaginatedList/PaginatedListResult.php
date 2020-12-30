<?php

namespace App\ReadModel\PaginatedList;

class PaginatedListResult
{

    public $rows;

    public $total;

    public $perPage;

    public $page;


    public function __construct(array $rows, int $total, int $page, int $perPage)
    {
        $this->rows    = $rows;
        $this->total   = $total;
        $this->perPage = $perPage;
        $this->page    = $page;
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}