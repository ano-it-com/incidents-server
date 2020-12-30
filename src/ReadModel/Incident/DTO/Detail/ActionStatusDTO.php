<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class ActionStatusDTO
{

    public $id;

    public $code;

    public $title;

    public $createdAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $createdBy;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\GroupDTO")
     */
    public $responsibleGroup;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $responsibleUser;

    public $ttl;
}