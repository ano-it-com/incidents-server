<?php

namespace App\UserActions\IncidentActionTaskContext\Actions;

use App\Domain\Action\Status\ActionStatusCorrection;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Entity\Incident\Action\ActionTask;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionTaskDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionTaskRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class SetActionTaskStatusUserAction extends AbstractActionTaskUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, ActionTaskDTO $actionTaskDTO, UserPermissions $userPermissions): array
    {
        return [
            'canSetActionTaskStatus' => function () use ($incidentActionDTO, $userPermissions){
                if (!$userPermissions->can('is_executor')) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                if (!in_array($currentActionStatusCode, [ActionStatusInWork::getCode(), ActionStatusCorrection::getCode()], true)) {
                    return false;
                }

                return true;
            }
        ];
    }

    public function execute(ActionTaskDTO $actionTaskDTO, $statusCode, UserInterface $user): void
    {
        /** @var ActionTaskRepository $repository */
        $repository = $this->em->getRepository(ActionTask::class);
        $actionTask = $repository->find($actionTaskDTO->id);

        if (!$actionTask) {
            throw new InvalidArgumentException("Action task with id {$actionTaskDTO->id} not found");
        }

        $this->em->beginTransaction();
        try {
            $status = $this->incidentService->createActionTaskStatus($actionTask, $statusCode, $user);
            $actionTask->setStatus($status);

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
