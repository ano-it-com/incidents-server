<?php

namespace App\UserActions\IncidentContext\Actions;

use App\Domain\Action\Status\ActionStatusClosed;
use App\Domain\Incident\Status\IncidentStatusClosed;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentTypeDTO;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class CloseIncidentUserAction extends AbstractIncidentUserAction
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
            'canCloseIncident' => function () use ($userPermissions, $incidentDTO) {
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                if(!$userPermissions->canByStatus('can_edit_incident_by_status', $incidentDTO->status->code)){
                    return false;
                }


                if ($incidentDTO->status->code === IncidentStatusClosed::getCode()) {
                    return false;
                }

                foreach ($incidentDTO->actions as $action) {
                    if (! in_array($action->status->code, [ActionStatusClosed::getCode()])) {
                        return false;
                    }
                }

                return true;
            }
        ];
    }

    public function execute(IncidentDTO $incidentDTO, UserInterface $user)
    {
        /** @var IncidentRepository $repository */
        $repository = $this->em->getRepository(Incident::class);
        $incident = $repository->find($incidentDTO->id);

        if (!$incident) {
            throw new InvalidArgumentException("Incident with id {$incidentDTO->id} not found");
        }

        $this->em->beginTransaction();
        try {
            $status = $this->incidentService->createIncidentStatus($incident, IncidentStatusClosed::getCode(), $user);
            $incident->setStatus($status);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
