<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Domain\ActionTask\Status\ActionTaskStatusEmpty;
use App\Domain\Action\Status\ActionStatusApproving;
use App\Domain\Action\Status\ActionStatusCorrection;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangeActionToApprovingUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canChangeActionToApproving' => function () use ($userPermissions, $incidentDTO, $incidentActionDTO) {
                if (!$userPermissions->can('is_executor')) {
                    return false;
                }

                $responsibleUser = $incidentActionDTO->responsibleUser;

                if (!$responsibleUser) {
                    return false;
                }

                if ($userPermissions->getUser()->getId() !== $responsibleUser->id) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                if (!in_array($currentActionStatusCode, [ActionStatusInWork::getCode(), ActionStatusCorrection::getCode()], true)) {
                    return false;
                }

                foreach ($incidentActionDTO->actionTasks as $actionTask) {
                    $actionTaskStatus = $actionTask->status;
                    if (!$actionTaskStatus) {
                        return false;
                    }

                    if ($actionTaskStatus->code === ActionTaskStatusEmpty::getCode()) {
                        return false;
                    }
                }

                return true;
            }
        ];
    }

    /**
     * @param ActionDTO[] $actionDTOs
     * @param UserInterface $user
     * @throws \Throwable
     */
    public function execute(array $actionDTOs, UserInterface $user): void
    {
        /** @var ActionRepository $repository */
        $repository = $this->em->getRepository(Action::class);
        $actionIds = array_map(function (ActionDTO $actionDTO) {
            return $actionDTO->id;
        }, $actionDTOs);

        $actions = $repository->findBy(['id' => $actionIds]);
        $responsibleGroupSupervisor = current($this->groupProvider->getForPermission('is_supervisor'));

        $this->em->beginTransaction();
        try {
            foreach ($actions as $action) {
                $status = $this->incidentService->createActionStatus($action, ActionStatusApproving::getCode(), $user, $responsibleGroupSupervisor);
                $action->setStatus($status);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
