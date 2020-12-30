<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class ActionTaskDTO
{

    public $id;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionTaskTypeDTO")
     */
    public $type;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionTaskStatusDTO", skipLoading=true)
     */
    public $status;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionTaskStatusDTO")
     */
    public $statuses;

    public $inputData;

    public $reportData;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\FileDTO", entity="App\Entity\File\File", loader="App\ReadModel\Incident\RelationLoaders\FilesActionTaskRelationLoader")
     */
    public $filesReport;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\FileDTO", entity="App\Entity\File\File", loader="App\ReadModel\Incident\RelationLoaders\FilesActionTaskRelationLoader")
     */
    public $filesInput;

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

    public $deleted;

    /**
     * @var array
     */
    public $rights;
}