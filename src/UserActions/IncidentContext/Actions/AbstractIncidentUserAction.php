<?php

namespace App\UserActions\IncidentContext\Actions;

use App\Domain\Incident\IncidentStatusLocator;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Loaders\Incident\IncidentLoader;
use App\Security\Permissions\PermissionsProvider;
use App\Services\IncidentService;
use App\UserActions\IncidentContext\IncidentContextUserActionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractIncidentUserAction implements IncidentContextUserActionInterface
{
    protected PermissionsProvider $permissionsProvider;

    protected IncidentService $incidentService;

    protected EntityManagerInterface $em;

    protected IncidentLoader $incidentLoader;

    protected EventDispatcherInterface $eventDispatcher;

    protected IncidentStatusLocator $incidentStatusLocator;

    public function __construct(
        EntityManagerInterface $em,
        PermissionsProvider $permissionsProvider,
        IncidentService $incidentService,
        IncidentLoader $incidentLoader,
        EventDispatcherInterface $eventDispatcher,
        IncidentStatusLocator $incidentStatusLocator
    ) {
        $this->permissionsProvider = $permissionsProvider;
        $this->incidentService = $incidentService;
        $this->em = $em;
        $this->incidentLoader = $incidentLoader;
        $this->eventDispatcher = $eventDispatcher;
        $this->incidentStatusLocator = $incidentStatusLocator;
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param UserInterface $user
     * @return bool
     */
    public function can(IncidentDTO $incidentDTO, UserInterface $user): bool
    {
        $userPermission = $this->permissionsProvider->getUserPermissions($user);
        $rights = $this->exportRights($incidentDTO, $userPermission);
        foreach ($rights as $rightCheck) {
            if ($rightCheck() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $incidentId
     * @param UserInterface $user
     * @return IncidentDTO
     */
    public function getDtoIfCan(int $incidentId, UserInterface $user): IncidentDTO
    {
        $incident = $this->incidentLoader->getById($incidentId, $user);
        if (!$incident) {
            throw new AccessDeniedException;
        }

        if (!$this->can($incident, $user)) {
            throw new AccessDeniedException;
        }

        return $incident;
    }
}