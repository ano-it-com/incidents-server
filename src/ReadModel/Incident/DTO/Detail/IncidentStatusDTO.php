<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class IncidentStatusDTO
{
    public $id;

    public $code;

    public $title;

    public $createdAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $createdBy;

    public $ttl;
}