<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class ActionDTO
{

    public $id;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionTypeDTO")
     */
    public $type;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionStatusDTO")
     */
    public $status;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionTaskDTO")
     */
    public $actionTasks;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\CommentDTO", entity="App\Entity\Incident\Comment\Comment", loader="App\ReadModel\Incident\RelationLoaders\CommentsRelationLoader")
     */
    public $comments;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\GroupDTO")
     */
    public $responsibleGroup;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\UserDTO")
     */
    public $responsibleUser;

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

    public $templateId;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\FileDTO", entity="App\Entity\File\File", loader="App\ReadModel\Incident\RelationLoaders\FilesRelationLoader")
     */
    public $files;

    /**
     * @var array
     */
    public $rights;

    public function getTaskById($id): ?ActionTaskDTO
    {
        /** @var ActionTaskDTO $task */
        foreach ($this->actionTasks as $task) {
            if($task->id == $id){
                return $task;
            }
        }
        return null;
    }
}