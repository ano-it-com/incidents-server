<?php

namespace App\UserActions;

use App\Entity\Security\User;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionTaskDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\PermissionsProvider;
use App\Security\Permissions\UserPermissions;
use App\UserActions\IncidentActionContext\IncidentActionContextUserActionsLocator;
use App\UserActions\IncidentActionTaskContext\IncidentActionTaskContextUserActionsLocator;
use App\UserActions\IncidentContext\IncidentContextUserActionsLocator;

class UserActionsRightsSetter
{

    /**
     * @var IncidentContextUserActionsLocator
     */
    private $incidentContextUserActionsLocator;

    /**
     * @var IncidentActionContextUserActionsLocator
     */
    private $incidentActionContextUserActionsLocator;

    /**
     * @var IncidentActionTaskContextUserActionsLocator
     */
    private $incidentActionTaskContextUserActionsLocator;

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;


    public function __construct(
        IncidentContextUserActionsLocator $incidentContextUserActionsLocator,
        IncidentActionContextUserActionsLocator $incidentActionContextUserActionsLocator,
        IncidentActionTaskContextUserActionsLocator $incidentActionTaskContextUserActionsLocator,
        PermissionsProvider $permissionsProvider
    ) {
        $this->incidentContextUserActionsLocator = $incidentContextUserActionsLocator;
        $this->incidentActionContextUserActionsLocator = $incidentActionContextUserActionsLocator;
        $this->incidentActionTaskContextUserActionsLocator = $incidentActionTaskContextUserActionsLocator;
        $this->permissionsProvider               = $permissionsProvider;
    }


    /**
     * @param IncidentDTO[] $dtos
     * @param User $user
     */
    public function setRightsToDTOs(array $dtos, User $user): void
    {
        $permissions = $this->permissionsProvider->getUserPermissions($user);
        $this->setRightsToIncident($dtos, $permissions);
    }

    protected function setRightsToIncident(array $dtos, UserPermissions $permissions){
        /** @var IncidentDTO $incidentDto */
        foreach ($dtos as $incidentDto) {
            $rights = [];
            foreach ($this->incidentContextUserActionsLocator->getAllClasses() as $userActionClass) {
                if ( ! $userActionClass::supports($incidentDto, $permissions)) {
                    continue;
                }

                $userAction = $this->incidentContextUserActionsLocator->get($userActionClass);

                $rights[] = $userAction->exportRights($incidentDto, $permissions);
            }

            $incidentDto->rights = $this->getFlatRights($rights);
            $this->setRightsToAction($incidentDto, $incidentDto->actions, $permissions);
        }
    }

    protected function setRightsToAction(IncidentDTO $parentIncidentDTO, array $actionDtos, UserPermissions $permissions){
        /** @var ActionDTO $actionDto */
        foreach ($actionDtos as $actionDto) {
            $rights = [];
            foreach ($this->incidentActionContextUserActionsLocator->getAllClasses() as $userActionClass) {
                if ( ! $userActionClass::supports($parentIncidentDTO, $actionDto, $permissions)) {
                    continue;
                }

                $userAction = $this->incidentActionContextUserActionsLocator->get($userActionClass);

                $rights[] = $userAction->exportRights($parentIncidentDTO, $actionDto, $permissions);
            }

            $actionDto->rights = $this->getFlatRights($rights);
            $this->setRightsToActionTask($parentIncidentDTO, $actionDto, $actionDto->actionTasks, $permissions);
        }
    }

    protected function setRightsToActionTask(IncidentDTO $parentIncidentDTO, ActionDTO $parentActionDTO, array $actionTaskDtos, UserPermissions $permissions){
        /** @var ActionTaskDTO $taskDto */
        foreach ($actionTaskDtos as $taskDto) {
            $rights = [];
            foreach ($this->incidentActionTaskContextUserActionsLocator->getAllClasses() as $userActionClass) {
                if ( ! $userActionClass::supports($parentIncidentDTO, $parentActionDTO, $taskDto, $permissions)) {
                    continue;
                }

                $userAction = $this->incidentActionTaskContextUserActionsLocator->get($userActionClass);

                $rights[] = $userAction->exportRights($parentIncidentDTO, $parentActionDTO, $taskDto, $permissions);
            }

            $taskDto->rights = $this->getFlatRights($rights);
        }
    }

    protected function getFlatRights($rights){
        $flatRights = [];

        foreach ($rights as $rightsItem) {
            foreach ($rightsItem as $code => $checkFunc) {
                $flatRights[$code] = $checkFunc();
            }
        }
        return $flatRights;
    }
}