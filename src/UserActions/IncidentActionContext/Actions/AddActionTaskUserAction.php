<?php


namespace App\UserActions\IncidentActionContext\Actions;

use App\Controller\Api\Request\Incident\AddActionTasksForActionDTO;
use App\Domain\Action\Status\ActionStatusClosed;
use App\Entity\Incident\Action\Action;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Security\Permissions\UserPermissions;
use App\UserActions\IncidentActionContext\Event\ActionContextEvent;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class AddActionTaskUserAction extends AbstractIncidentActionUserAction
{

    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canAddActionTask' => function () use ($userPermissions, $incidentDTO, $incidentActionDTO) {
                if (!$userPermissions->can('is_supervisor')) {
                    return false;
                }

                $currentActionStatusCode = $incidentActionDTO->status->code;

                return $currentActionStatusCode !== ActionStatusClosed::getCode();
            }
        ];
    }

    public function execute(IncidentDTO $incidentDTO, ActionDTO $actionDTO, AddActionTasksForActionDTO $tasksDTO, UserInterface $user)
    {
        /** @var ActionRepository $repository */
        $repository = $this->em->getRepository(Action::class);
        $action = $repository->find($actionDTO->id);

        if (!$action) {
            throw new InvalidArgumentException("Action with id {$actionDTO->id} not found");
        }

        $this->em->beginTransaction();
        try {
            $newTasks = [];
            foreach ($tasksDTO->actionTasks as $actionTaskDTO) {
                $task = $this->incidentService->createActionTask($actionTaskDTO, $action, $user);
                $action->addActionTask($task);

                $newTasks[] = $task;
            }

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        foreach ($newTasks as $newTask) {
            if (!empty($newTask->getReportData())) {
                $this->eventDispatcher->dispatch(
                    new ActionContextEvent($this, $incidentDTO, $actionDTO, $user),
                    ActionContextEvent::ACTION_COMPLETE_NOTIFICATION_EVENT
                );
            }
        }
    }
}
