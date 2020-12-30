<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Domain\Action\Status\ActionStatusApproving;
use App\Domain\Action\Status\ActionStatusClosed;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangeActionToCloseUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canChangeActionToClose' => function() use ($userPermissions, $incidentDTO, $incidentActionDTO){
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                return $currentActionStatusCode == ActionStatusApproving::getCode();
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
                $status = $this->incidentService->createActionStatus($action, ActionStatusClosed::getCode(), $user, $responsibleGroupSupervisor);
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
