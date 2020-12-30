<?php

namespace App\ReadModel\Incident\DTO\Detail;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;

class IncidentDTO
{

    public $id;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\IncidentTypeDTO")
     */
    public $type;

    public $info;

    public $date;

    public $title;

    public $description;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\GroupDTO")
     */
    public $responsibleGroups;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\IncidentStatusDTO")
     */
    public $status;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\IncidentReferenceDTO")
     */
    public $repeatedIncident;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\ActionDTO", entity="App\Entity\Incident\Action\Action", loader="App\ReadModel\Incident\RelationLoaders\ActionsRelationLoader")
     */
    public $actions;

    /**
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\CommentDTO", entity="App\Entity\Incident\Comment\Comment", loader="App\ReadModel\Incident\RelationLoaders\CommentsRelationLoader")
     */
    public $comments;

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
     * @DTO(class="App\ReadModel\Incident\DTO\Detail\FileDTO", entity="App\Entity\File\File", loader="App\ReadModel\Incident\RelationLoaders\FilesRelationLoader")
     */
    public $files;

    /**
     * @var array
     */
    public $rights;


    public function getActionById($id): ?ActionDTO
    {
        /** @var ActionDTO $action */
        foreach ($this->actions as $action) {
            if($action->id == $id){
                return $action;
            }
        }
        return null;
    }

}