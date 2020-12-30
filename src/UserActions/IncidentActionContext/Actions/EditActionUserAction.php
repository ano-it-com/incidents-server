<?php

namespace App\UserActions\IncidentActionContext\Actions;

use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\UserPermissions;

class EditActionUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canEditAction' => function() use ($userPermissions, $incidentDTO, $incidentActionDTO){
                //TODO Придумать получение прав из других UserAction
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                if(!$userPermissions->canByStatus('can_edit_incident_by_status', $incidentDTO->status->code)){
                    return false;
                }

                return $userPermissions->canByStatus('can_edit_action_by_status', $incidentActionDTO->status->code);
            }
        ];
    }
}
