<?php

namespace App\UserActions\ContextFree\Actions;

use App\Controller\Api\Request\Incident\CreateIncidentDTO;
use App\Entity\Incident\Incident;
use App\Entity\Security\User;
use App\Security\Permissions\UserPermissions;
use App\Services\IncidentService;
use App\UserActions\ContextFree\ContextFreeUserActionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateIncidentUserAction implements ContextFreeUserActionInterface
{
    private IncidentService $incidentService;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(IncidentService $incidentService, EventDispatcherInterface $eventDispatcher)
    {
        $this->incidentService = $incidentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function exportRights(User $user, UserPermissions $userPermissions): array
    {
        return [
            'canCreateIncident' => function () use ($userPermissions) {
                return $userPermissions->can('can_create_incidents');
            }
        ];
    }

    public function execute(CreateIncidentDTO $createIncidentDTO, UserInterface $user): Incident
    {
        return $this->incidentService->create($createIncidentDTO, $user);
    }
}
