<?php

namespace App\UserActions\IncidentContext\Actions;

use App\Controller\Api\Request\Incident\CreateIncidentDTO;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentTypeDTO;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class EditIncidentUserAction extends AbstractIncidentUserAction
{
    public static function supports(IncidentDTO $incidentDTO, UserPermissions $userPermissions): bool
    {
        /** @var IncidentTypeDTO $type */
        $type = $incidentDTO->type;

        return $type->handler === 'incident';
    }

    public function exportRights(IncidentDTO $incidentDTO, UserPermissions $userPermissions): array
    {
        return [
            'canEditIncident' => function() use ($incidentDTO, $userPermissions){
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                return $userPermissions->canByStatus('can_edit_incident_by_status', $incidentDTO->status->code);
            }
        ];
    }

    public function execute(IncidentDTO $incidentDTO, CreateIncidentDTO $createIncidentDTO, UserInterface $user)
    {
        /** @var IncidentRepository $repository */
        $repository = $this->em->getRepository(Incident::class);
        $incident = $repository->find($incidentDTO->id);

        if (!$incident) {
            throw new InvalidArgumentException("Incident with id {$incidentDTO->id} not found");
        }

        $this->em->beginTransaction();
        try {
            $this->incidentService->update($incident, $createIncidentDTO, $user);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
