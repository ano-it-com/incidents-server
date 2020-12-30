<?php

namespace App\UserActions\IncidentActionContext;

use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\UserPermissions;

interface IncidentActionContextUserActionInterface
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool;

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array;
}