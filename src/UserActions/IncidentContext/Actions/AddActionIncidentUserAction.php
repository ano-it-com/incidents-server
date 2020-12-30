<?php

namespace App\UserActions\IncidentContext\Actions;

use App\Controller\Api\Request\Incident\CreateActionDTO;
use App\Controller\Api\Request\Incident\CreateActionsForIncidentDTO;
use App\Domain\Action\Status\ActionStatusClosed;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentTypeDTO;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class AddActionIncidentUserAction extends AbstractIncidentUserAction
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
            'canAddAction' => function () use ($incidentDTO, $userPermissions) {
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                if (!$userPermissions->canByStatus('can_edit_incident_by_status', $incidentDTO->status->code)) {
                    return false;
                }

                return $incidentDTO->status->code != ActionStatusClosed::getCode();
            }
        ];
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param CreateActionsForIncidentDTO $createActionsForIncidentDTO
     * @param UserInterface $user
     * @return Action[]
     * @throws Throwable
     */
    public function execute(IncidentDTO $incidentDTO, CreateActionsForIncidentDTO $createActionsForIncidentDTO, UserInterface $user): array
    {
        /** @var IncidentRepository $repository */
        $repository = $this->em->getRepository(Incident::class);
        $incident = $repository->find($incidentDTO->id);

        if (!$incident) {
            throw new InvalidArgumentException("Incident with id {$incidentDTO->id} not found");
        }

        $created = [];
        $this->em->beginTransaction();
        try {
            /** @var CreateActionDTO $actionDTO */
            foreach ($createActionsForIncidentDTO->actions as $actionDTO) {
                $actions = $this->incidentService->createActions($actionDTO, $incident, $user);

                foreach ($actions as $action) {
                    $incident->addAction($action);
                    $created[] = $action;
                }
            }

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
        return $created;
    }
}
