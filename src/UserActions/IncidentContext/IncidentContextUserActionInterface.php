<?php

namespace App\UserActions\IncidentContext;

use App\Entity\Security\User;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\UserPermissions;

interface IncidentContextUserActionInterface
{
    public static function supports(IncidentDTO $incidentDTO, UserPermissions $userPermissions): bool;

    public function exportRights(IncidentDTO $incidentDTO, UserPermissions $userPermissions): array;
}