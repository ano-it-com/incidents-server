<?php

namespace App\UserActions\IncidentActionContext\Actions;

use App\Controller\Api\Request\Incident\ActionWithCommentDTO;
use App\Domain\Action\Status\ActionStatusApproving;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class ChangeActionToCorrectionUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canChangeActionToCorrection' => function() use ($userPermissions, $incidentDTO, $incidentActionDTO){
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                return $currentActionStatusCode == ActionStatusApproving::getCode();
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

        // ищем предыдущий статус
        if (!($previousStatus = $action->getPreviousStatus())) {
            throw new InvalidArgumentException('Can\'t found status to return back');
        }

        $responsibleGroup = $previousStatus->getResponsibleGroup();
        $this->em->beginTransaction();
        try {
            $this->incidentService->createActionComment($incidentDTO, $actionDTO, $actionWithCommentDTO, $user, $responsibleGroup);
            $status = $this->incidentService->createActionStatus(
                $action,
                $previousStatus->getCode(),
                $user, $responsibleGroup,
                $previousStatus->getResponsibleUser()
            );
            $action->setStatus($status);

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
