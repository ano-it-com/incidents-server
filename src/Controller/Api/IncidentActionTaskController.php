<?php

namespace App\Controller\Api;

use App\Controller\Api\Request\Incident\UpdateActionTaskReportDTO;
use App\Infrastructure\Response\ResponseFactory;
use App\UserActions\IncidentActionTaskContext\Actions\EditActionTaskReportUserAction;
use App\UserActions\IncidentActionTaskContext\Actions\SetActionTaskStatusUserAction;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Throwable;

class IncidentActionTaskController extends AbstractController
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Обновление статуса рекомендации
     *
     * @Route("/incident/{incidentId}/action/{actionId}/task/{taskId}/status/{code}", name="ims_incident_action_task_set_status", methods={"POST"})
     *
     * @param int $incidentId
     * @param int $actionId
     * @param int $taskId
     * @param string $code
     * @param SetActionTaskStatusUserAction $userAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function setActionTaskStatus(int $incidentId, int $actionId, int $taskId, string $code, SetActionTaskStatusUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list(, , $tasks) = $userAction->getDtoIfCan($incidentId, $actionId, $taskId, $user);

        $userAction->execute(current($tasks), $code, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Обновление рекомендации
     *
     * @Route("/incident/{incidentId}/action/{actionId}/task/{taskId}/report", name="ims_incident_action_task_update_report", methods={"PUT"})
     * @OA\RequestBody (
     *     @Model(type=UpdateActionTaskReportDTO::class),
     * )
     *
     * @param int $incidentId
     * @param int $actionId
     * @param int $taskId
     * @param UpdateActionTaskReportDTO $updateActionTaskDTO
     * @param EditActionTaskReportUserAction $userAction
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateActionTaskReport(int $incidentId, int $actionId, int $taskId, UpdateActionTaskReportDTO $updateActionTaskDTO, EditActionTaskReportUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list(, , $tasks) = $userAction->getDtoIfCan($incidentId, $actionId, $taskId, $user);

        $userAction->execute(current($tasks), $updateActionTaskDTO, $user);
        return ResponseFactory::success([]);
    }
}