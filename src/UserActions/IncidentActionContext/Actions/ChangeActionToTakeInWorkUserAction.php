<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Domain\Action\Status\ActionStatusInWork;
use App\Domain\Action\Status\ActionStatusNew;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use App\UserActions\IncidentActionContext\Event\ActionContextEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangeActionToTakeInWorkUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canChangeActionToTakeInWork' => function () use ($userPermissions, $incidentDTO, $incidentActionDTO) {
                if (!$userPermissions->can('is_executor')) {
                    return false;
                }

                if ($incidentActionDTO->responsibleUser) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                return $currentActionStatusCode == ActionStatusNew::getCode();
            }
        ];
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param ActionDTO[] $actionDTOs
     * @param UserInterface $user
     * @throws \Throwable
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
            foreach ($actions as $action) {
                $responsibleGroup = $action->getResponsibleGroup();
                if (!$responsibleGroup) {
                    throw new \InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $status = $this->incidentService->createActionStatus($action, ActionStatusInWork::getCode(), $user, $responsibleGroup, $user);
                $action->setResponsibleUser($user);
                $action->setStatus($status);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
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
