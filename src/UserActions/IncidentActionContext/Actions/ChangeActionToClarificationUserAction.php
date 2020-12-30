<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Controller\Api\Request\Incident\ActionWithCommentDTO;
use App\Domain\Action\Status\ActionStatusApproving;
use App\Domain\Action\Status\ActionStatusClarification;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use App\UserActions\IncidentActionContext\Event\ActionContextEvent;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class ChangeActionToClarificationUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canActionToClarificationUserAction' => function () use ($userPermissions, $incidentDTO, $incidentActionDTO) {
                $currentActionStatusCode = $incidentActionDTO->status->code;

                if ($userPermissions->can('is_executor')) {
                    if ($currentActionStatusCode !== ActionStatusInWork::getCode()) {
                        return false;
                    }

                    $responsibleUser = $incidentActionDTO->responsibleUser;

                    if ( ! $responsibleUser) {
                        return false;
                    }

                    if ($userPermissions->getUser()->getId() !== $responsibleUser->id) {
                        return false;
                    }

                    return true;
                }

                if ($userPermissions->can('is_moderator')) {
                    return !($currentActionStatusCode !== ActionStatusApproving::getCode());
                }

                return false;
            }
        ];
    }

    public function execute(IncidentDTO $incidentDTO, ActionDTO $actionDTO, ActionWithCommentDTO $actionWithCommentDTO, UserInterface $user): void
    {
        /** @var ActionRepository $repository */
        $repository = $this->em->getRepository(Action::class);
        $action = $repository->find($actionDTO->id);

        if (!$action) {
            throw new InvalidArgumentException("Action with id {$actionDTO->id} not found");
        }

        $responsibleGroupSupervisor = current($this->groupProvider->getForPermission('is_supervisor'));

        $this->em->beginTransaction();
        try {
            $comment = $this->incidentService->createActionComment($incidentDTO, $actionDTO, $actionWithCommentDTO, $user, $responsibleGroupSupervisor);
            $status = $this->incidentService->createActionStatus($action, ActionStatusClarification::getCode(), $user, $responsibleGroupSupervisor);
            $action->setStatus($status);

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        $this->eventDispatcher->dispatch(
            new ActionContextEvent($this, $incidentDTO, $actionDTO, $user, ['comment' => $comment]),
            ActionContextEvent::ACTION_COMPLETE_NOTIFICATION_EVENT
        );
    }
}
