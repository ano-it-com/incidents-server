<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class ActionTaskStatusDTO
{

    public $id;

    public $code;

    public $createdAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $createdBy;

}