<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Domain\Action\Status\ActionStatusDraft;
use App\Domain\Action\Status\ActionStatusNew;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use App\UserActions\IncidentActionContext\Event\ActionContextEvent;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class ChangeActionToWorkUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canChangeActionToWork' => function () use ($userPermissions, $incidentDTO, $incidentActionDTO) {
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                return $incidentActionDTO->status->code == ActionStatusDraft::getCode();
            }
        ];
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param ActionDTO[] $actionDTOs
     * @param UserInterface $user
     * @throws Throwable
     */
    public function execute(IncidentDTO $incidentDTO, array $actionDTOs, UserInterface $user): void
    {
        /** @var ActionRepository $repository */
        $repository = $this->em->getRepository(Action::class);
        $actionIds = array_map(function (ActionDTO $actionDTO) {
            return $actionDTO->id;
        }, $actionDTOs);

        $actions = $repository->findBy(['id' => $actionIds]);


        $this->em->beginTransaction();
        try {
            if ($incidentDTO->status->code !== IncidentStatusInWork::getCode()) {
                /** @var IncidentRepository $incidentRepository */
                $incidentRepository = $this->em->getRepository(Incident::class);
                $incident = $incidentRepository->find($incidentDTO->id);

                $status = $this->incidentService->createIncidentStatus($incident, IncidentStatusInWork::getCode(), $user);
                $incident->setStatus($status);
            }

            foreach ($actions as $action) {
                $responsibleGroup = $action->getResponsibleGroup();
                if (!$responsibleGroup) {
                    throw new InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $status = $this->incidentService->createActionStatus($action, ActionStatusNew::getCode(), $user, $responsibleGroup);
                $action->setStatus($status);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        foreach ($actionDTOs as $actionDTO) {
            $this->eventDispatcher->dispatch(
                new ActionContextEvent($this, $incidentDTO, $actionDTO, $user),
                ActionContextEvent::ACTION_COMPLETE_NOTIFICATION_EVENT
            );
        }
    }
}
