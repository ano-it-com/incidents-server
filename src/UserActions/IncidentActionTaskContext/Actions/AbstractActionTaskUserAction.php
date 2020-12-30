<?php

namespace App\UserActions\IncidentActionTaskContext\Actions;

use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionTaskDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Loaders\Incident\IncidentLoader;
use App\Security\Permissions\PermissionsProvider;
use App\Services\FileService;
use App\Services\IncidentService;
use App\UserActions\IncidentActionTaskContext\IncidentActionTaskContextUserActionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractActionTaskUserAction implements IncidentActionTaskContextUserActionInterface
{
    protected PermissionsProvider $permissionsProvider;

    protected IncidentService $incidentService;

    protected EntityManagerInterface $em;

    protected IncidentLoader $incidentLoader;

    protected FileService $fileService;

    public function __construct(
        EntityManagerInterface $em,
        PermissionsProvider $permissionsProvider,
        IncidentService $incidentService,
        IncidentLoader $incidentLoader,
        FileService $fileService
    ) {
        $this->permissionsProvider = $permissionsProvider;
        $this->incidentService = $incidentService;
        $this->em = $em;
        $this->incidentLoader = $incidentLoader;
        $this->fileService = $fileService;
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param ActionDTO $actionDTO
     * @param ActionTaskDTO|ActionTaskDTO[] $actionTaskDTOs
     * @param UserInterface $user
     * @return bool
     */
    public function can(IncidentDTO $incidentDTO, ActionDTO $actionDTO, $actionTaskDTOs, UserInterface $user): bool
    {
        $userPermission = $this->permissionsProvider->getUserPermissions($user);
        foreach ((array)$actionTaskDTOs as $actionTaskDTO) {
            $rights = $this->exportRights($incidentDTO, $actionDTO, $actionTaskDTO, $userPermission);
            foreach ($rights as $rightCheck) {
                if ($rightCheck() === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param int $incidentId
     * @param int $taskId
     * @param int[]|int $actionTaskIds
     * @param UserInterface $user
     * @return array
     */
    public function getDtoIfCan(int $incidentId, int $taskId, $actionTaskIds, UserInterface $user): array
    {
        $incident = $this->incidentLoader->getById($incidentId, $user);
        if (!$incident) {
            throw new AccessDeniedException;
        }
        if (!($action = $incident->getActionById($taskId))) {
            throw new AccessDeniedException;
        }

        $tasks = [];
        foreach ((array)$actionTaskIds as $taskId) {
            if (!($task = $action->getTaskById($taskId))) {
                throw new AccessDeniedException;
            }
            $tasks[] = $task;
        }

        if (!$this->can($incident, $action, $tasks, $user)) {
            throw new AccessDeniedException;
        }

        return [$incident, $action, $tasks];
    }
}
