<?php

namespace App\Controller\Api;

use App\Controller\Api\Request\Incident\ActionForActionsDTO;
use App\Controller\Api\Request\Incident\ActionWithCommentDTO;
use App\Controller\Api\Request\Incident\AddActionTasksForActionDTO;
use App\Infrastructure\Response\ResponseFactory;
use App\ReadModel\Incident\DTO\Detail\HistoryDTO;
use App\UserActions\IncidentActionContext\Actions\AddActionTaskUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToApprovingUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToBackFromClarificationUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToClarificationUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToCloseUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToCorrectionUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToTakeInWorkUserAction;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToWorkUserAction;
use App\UserActions\IncidentActionContext\Actions\GetHistoryActionUserAction;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Throwable;

class IncidentActionController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Добавление задачи для действия
     *
     * @Route("/incident/{incidentId}/action/{actionId}/task", name="ims_incident_action_task_add", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=AddActionTasksForActionDTO::class),
     * )
     *
     * @param $incidentId
     * @param $actionId
     * @param AddActionTasksForActionDTO $addActionTaskToActionDTO
     * @param AddActionTaskUserAction $userAction
     * @return JsonResponse
     */
    public function addActionTask($incidentId, $actionId, AddActionTasksForActionDTO $addActionTaskToActionDTO, AddActionTaskUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionId, $user);

        $userAction->execute($incident, current($actions), $addActionTaskToActionDTO, $user);
        return ResponseFactory::success([]);
    }

    /**
     * Запрос на одобрение действия
     *
     * @Route("/incident/{incidentId}/actions/approving", name="ims_incident_action_approving", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionForActionsDTO::class),
     * )
     *
     * @param $incidentId
     * @param ActionForActionsDTO $actionForActionsDTO
     * @param ChangeActionToApprovingUserAction $userAction
     * @return JsonResponse
     */
    public function approving($incidentId, ActionForActionsDTO $actionForActionsDTO, ChangeActionToApprovingUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list(, $actions) = $userAction->getDtoIfCan($incidentId, $actionForActionsDTO->actionIds, $user);

        $userAction->execute($actions, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Возврат действия с разъяснения
     *
     * @Route("/incident/{incidentId}/action/{actionId}/back-from-clarification", name="ims_incident_action_from_clarification", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionWithCommentDTO::class),
     * )
     *
     * @param $incidentId
     * @param $actionId
     * @param ActionWithCommentDTO $actionWithCommentDTO
     * @param ChangeActionToBackFromClarificationUserAction $userAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function backFromClarification($incidentId, $actionId, ActionWithCommentDTO $actionWithCommentDTO, ChangeActionToBackFromClarificationUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionId, $user);

        $userAction->execute($incident, current($actions), $actionWithCommentDTO, $user);

        return ResponseFactory::success([]);

    }

    /**
     * Отправить действие на разъяснение
     *
     * @Route("/incident/{incidentId}/action/{actionId}/clarification", name="ims_incident_action_clarification", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionWithCommentDTO::class),
     * )
     *
     * @param $incidentId
     * @param $actionId
     * @param ActionWithCommentDTO $actionWithCommentDTO
     *
     * @param ChangeActionToClarificationUserAction $userAction
     * @return JsonResponse
     */
    public function clarification($incidentId, $actionId, ActionWithCommentDTO $actionWithCommentDTO, ChangeActionToClarificationUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionId, $user);

        $userAction->execute($incident, current($actions), $actionWithCommentDTO, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Закрыть действия
     *
     * @Route("/incident/{incidentId}/actions/close", name="ims_incident_action_close", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionForActionsDTO::class),
     * )
     *
     * @param $incidentId
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @param ChangeActionToCloseUserAction $userAction
     * @return JsonResponse
     */
    public function close($incidentId, ActionForActionsDTO $actionForActionsDTO, ChangeActionToCloseUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list(, $actions) = $userAction->getDtoIfCan($incidentId, $actionForActionsDTO->actionIds, $user);

        $userAction->execute($actions, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Отправка действия на корректировку
     *
     * @Route("/incident/{incidentId}/action/{actionId}/correction", name="ims_incident_action_correction", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionWithCommentDTO::class),
     * )
     *
     * @param $incidentId
     * @param $actionId
     * @param ActionWithCommentDTO $actionWithCommentDTO
     * @param ChangeActionToCorrectionUserAction $userAction
     * @return JsonResponse
     */
    public function correction($incidentId, $actionId, ActionWithCommentDTO $actionWithCommentDTO, ChangeActionToCorrectionUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionId, $user);

        $userAction->execute($incident, current($actions), $actionWithCommentDTO, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Взять действие в работу
     *
     * @Route("/incident/{incidentId}/actions/take-in-work", name="ims_incident_action_take_in_work", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionForActionsDTO::class),
     * )
     *
     * @param $incidentId
     * @param ActionForActionsDTO $actionForActionsDTO
     * @param ChangeActionToTakeInWorkUserAction $userAction
     * @return JsonResponse
     */
    public function takeInWorkAsResponsibleUser($incidentId, ActionForActionsDTO $actionForActionsDTO, ChangeActionToTakeInWorkUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionForActionsDTO->actionIds, $user);

        $userAction->execute($incident, $actions, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Перевод действий в работу
     *
     * @Route("/incident/{incidentId}/actions/work", name="ims_incident_action_work", methods={"POST"})
     * @OA\RequestBody (
     *     @Model(type=ActionForActionsDTO::class),
     * )
     *
     * @param $incidentId
     * @param ActionForActionsDTO $actionForActionsDTO
     * @param ChangeActionToWorkUserAction $userAction
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function work($incidentId, ActionForActionsDTO $actionForActionsDTO, ChangeActionToWorkUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        list($incident, $actions) = $userAction->getDtoIfCan($incidentId, $actionForActionsDTO->actionIds, $user);

        $userAction->execute($incident, $actions, $user);

        return ResponseFactory::success([]);
    }

    /**
     * Получение истории статусов к действию
     *
     * @Route("/incident/{incidentId}/action/{actionId}/history", name="ims_incident_action_history", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns action history",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=HistoryDTO::class))
     *     )
     * )
     *
     * @param $incidentId
     * @param $actionId
     * @param GetHistoryActionUserAction $userAction
     *
     * @return JsonResponse
     */
    public function getHistory($incidentId, $actionId, GetHistoryActionUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        [, $actions] = $userAction->getDtoIfCan($incidentId, $actionId, $user);

        $history = $userAction->execute(current($actions), $user);

        return ResponseFactory::success($history);
    }
}
