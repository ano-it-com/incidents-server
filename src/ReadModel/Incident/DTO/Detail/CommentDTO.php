<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class CommentDTO
{

    public $id;

    public $text;

    public $createdAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $createdBy;

    public $updatedAt;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $updatedBy;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\GroupDTO")
     */
    public $targetGroup;

    public $deleted;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\FileDTO", entity="App\Entity\File\File", loader="App\ReadModel\Incident\RelationLoaders\FilesRelationLoader")
     */
    public $files;
}