<?php

namespace App\UserActions\IncidentActionTaskContext;

use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionTaskDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\UserPermissions;

interface IncidentActionTaskContextUserActionInterface
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): bool;

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): array;
}