<?php

namespace App\UserActions\IncidentActionContext\Actions;

use App\Domain\Action\ActionStatusLocator;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Loaders\Incident\IncidentLoader;
use App\Security\Permissions\PermissionsProvider;
use App\Services\IncidentService;
use App\Services\Providers\GroupProvider;
use App\UserActions\IncidentActionContext\IncidentActionContextUserActionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractIncidentActionUserAction implements IncidentActionContextUserActionInterface
{
    protected PermissionsProvider $permissionsProvider;

    protected IncidentService $incidentService;

    protected EntityManagerInterface $em;

    protected IncidentLoader $incidentLoader;

    protected GroupProvider $groupProvider;

    protected EventDispatcherInterface $eventDispatcher;

    protected ActionStatusLocator $actionStatusLocator;

    public function __construct(
        EntityManagerInterface $em,
        PermissionsProvider $permissionsProvider,
        IncidentService $incidentService,
        IncidentLoader $incidentLoader,
        GroupProvider $groupProvider,
        EventDispatcherInterface $eventDispatcher,
        ActionStatusLocator $actionStatusLocator
    ) {
        $this->permissionsProvider = $permissionsProvider;
        $this->incidentService = $incidentService;
        $this->em = $em;
        $this->incidentLoader = $incidentLoader;
        $this->groupProvider = $groupProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->actionStatusLocator = $actionStatusLocator;
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param ActionDTO|ActionDTO[] $incidentActionDTOs
     * @param UserInterface $user
     * @return bool
     */
    public function can(IncidentDTO $incidentDTO, $incidentActionDTOs, UserInterface $user): bool
    {
        $userPermission = $this->permissionsProvider->getUserPermissions($user);
        foreach ((array)$incidentActionDTOs as $incidentActionDTO) {
            $rights = $this->exportRights($incidentDTO, $incidentActionDTO, $userPermission);
            foreach ($rights as $rightCheck) {
                if ($rightCheck() === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $incidentId
     * @param int[]|int $actionIds
     * @param UserInterface $user
     * @return array
     */
    public function getDtoIfCan($incidentId, $actionIds, UserInterface $user): array
    {
        $incident = $this->incidentLoader->getById($incidentId, $user);
        if (!$incident) {
            throw new AccessDeniedException;
        }
        $actions = [];
        foreach ((array)$actionIds as $actionId) {
            if (!($action = $incident->getActionById($actionId))) {
                throw new AccessDeniedException;
            }
            $actions[] = $action;
        }

        if (!$this->can($incident, $actions, $user)) {
            throw new AccessDeniedException;
        }

        return [$incident, $actions];
    }
}
