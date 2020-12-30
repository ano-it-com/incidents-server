<?php

namespace App\ReadModel\Loaders;

interface PaginationCriteriaInterface extends CriteriaInterface
{

    public function getPage(): int;


    public function getPerPage(): int;
}