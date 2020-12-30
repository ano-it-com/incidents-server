<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class FileDTO
{

    public $id;

    public $ownerCode;

    public $ownerId;

    public $path;

    public $originalName;

    public $size;

    public $createdAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $createdBy;

    public $deleted;

    public $customField;

}