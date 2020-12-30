<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class HistoryDTO
{
    /**
     * @var HistoryCodeDTO
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\HistoryCodeDTO")
     */
    public $from;

    /**
     * @var HistoryCodeDTO
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\HistoryCodeDTO")
     */
    public $to;

    /**
     * @var UserDTO
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $initiatedBy;

    /**
     * @var int
     */
    public $initiatedAt;
}