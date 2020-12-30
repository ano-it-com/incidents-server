<?php

namespace App\UserActions\IncidentActionTaskContext\Actions;

use App\Domain\Action\Status\ActionStatusClosed;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionTaskDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Security\Permissions\UserPermissions;

class EditActionTaskUserAction extends AbstractActionTaskUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): array
    {
        return [
            'canEditActionTask' => function () use ($incidentActionDTO, $userPermissions){
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                if (in_array($currentActionStatusCode, [ActionStatusClosed::getCode()], true)) {
                    return false;
                }

                return true;
            }
        ];
    }
}
