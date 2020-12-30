<?php

namespace App\Controller\Api;

use App\Controller\Api\Request\Incident\CreateActionsForIncidentDTO;
use App\Controller\Api\Request\Incident\CreateIncidentDTO;
use App\Controller\Api\Request\ListParamsDTO;
use App\Entity\Incident\Action\Action;
use App\Infrastructure\Response\ResponseFactory;
use App\ReadModel\Incident\DTO\Detail\HistoryDTO;
use App\ReadModel\Loaders\Incident\Criteria\ArrayFilterCriteria;
use App\ReadModel\Loaders\Incident\Criteria\ArraySortingCriteria;
use App\ReadModel\Loaders\Incident\Criteria\BasicPaginationCriteria;
use App\ReadModel\Loaders\Incident\IncidentLoader;
use App\Repository\Incident\IncidentRepository;
use App\Services\IncidentOptionsService;
use App\UserActions\ContextFree\Actions\CreateIncidentUserAction;
use App\UserActions\IncidentContext\Actions\AddActionIncidentUserAction;
use App\UserActions\IncidentContext\Actions\CloseIncidentUserAction;
use App\UserActions\IncidentContext\Actions\EditIncidentUserAction;
use App\UserActions\IncidentContext\Actions\GetHistoryIncidentUserAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Throwable;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class IncidentController extends AbstractController
{
    private IncidentOptionsService $incidentOptionsService;

    private IncidentRepository $incidentRepository;

    private Security $security;

    public function __construct(
        IncidentOptionsService $incidentOptionsService,
        IncidentRepository $incidentRepository,
        Security $security
    ) {
        $this->incidentOptionsService = $incidentOptionsService;
        $this->incidentRepository = $incidentRepository;
        $this->security = $security;
    }

    /**
     * Создание инцидента
     *
     * @Route("/incident", name="ims_incident_create", methods={"POST"})
     * @OA\RequestBody(
     *     @Model(type=CreateIncidentDTO::class),
     * )
     *
     * @param CreateIncidentDTO $createIncidentDTO
     * @param CreateIncidentUserAction $userAction
     * @return JsonResponse
     */
    public function create(CreateIncidentDTO $createIncidentDTO, CreateIncidentUserAction $userAction): JsonResponse
    {
        $incident = $userAction->execute($createIncidentDTO, $this->security->getUser());

        return ResponseFactory::success(['id' => $incident->getId()]);
    }


    /**
     * Обновление инцидента
     *
     * @Route("/incident/{incidentId}", name="ims_incident_update", methods={"PUT"})
     * @OA\RequestBody(
     *     @Model(type=CreateIncidentDTO::class),
     * )
     *
     * @param $incidentId
     * @param CreateIncidentDTO $createIncidentDTO
     * @param EditIncidentUserAction $userAction
     * @return JsonResponse
     */
    public function update($incidentId, CreateIncidentDTO $createIncidentDTO, EditIncidentUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        $incident = $userAction->getDtoIfCan($incidentId, $user);

        $userAction->execute($incident, $createIncidentDTO, $user);

        return ResponseFactory::success(['id' => $incident->id]);
    }

    /**
     * Закрытие инцидента
     *
     * @Route("/incident/{incidentId}/close", name="ims_incident_close", methods={"POST"})
     *
     * @param $incidentId
     * @param CloseIncidentUserAction $userAction
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function close($incidentId, CloseIncidentUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        $incident = $userAction->getDtoIfCan($incidentId, $user);

        $userAction->execute($incident, $user);

        return ResponseFactory::success(['id' => $incident->id]);
    }

    /**
     * Добавление действий для инцидентов
     *
     * @Route("/incident/{incidentId}/actions", name="ims_incident_action_add", methods={"PUT"})
     * @OA\RequestBody(
     *     @Model(type=CreateActionsForIncidentDTO::class),
     * )
     *
     * @param $incidentId
     * @param CreateActionsForIncidentDTO $actionsForIncidentDTO
     * @param AddActionIncidentUserAction $userAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function addActions($incidentId, CreateActionsForIncidentDTO $actionsForIncidentDTO, AddActionIncidentUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        $incident = $userAction->getDtoIfCan($incidentId, $user);

        $actions = $userAction->execute($incident, $actionsForIncidentDTO, $user);
        $ids = array_map(static function (Action $action) {
            return $action->getId();
        }, $actions);

        return ResponseFactory::success([$ids]);

    }

    /**
     * Получение мета данных по инциденту
     *
     * @Route("/incident/options", name="ims_incident_options", methods={"GET"})
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function getOptions(): JsonResponse
    {
        $meta = $this->incidentOptionsService->getOptions($this->security->getUser());

        return ResponseFactory::success($meta);
    }

    /**
     * Получение списка инцидентов
     *
     * @Route("/incident", name="ims_incident_list", methods={"GET"})
     * @OA\RequestBody(
     *     @Model(type=ListParamsDTO::class),
     * )
     *
     * @param ListParamsDTO $listParamsDTO
     * @param IncidentLoader $incidentLoader
     *
     * @return JsonResponse
     */
    public function getList(ListParamsDTO $listParamsDTO, IncidentLoader $incidentLoader): JsonResponse
    {
        $paginatedList = $incidentLoader->getPaginatedList(
            new ArrayFilterCriteria($listParamsDTO->filters),
            new ArraySortingCriteria([$listParamsDTO->sortField, $listParamsDTO->sortDir]),
            new BasicPaginationCriteria($listParamsDTO->page, $listParamsDTO->perPage),
            $this->security->getUser()
        );

        return ResponseFactory::success($paginatedList->toArray());
    }

    /**
     * Получение одного инцидента
     *
     * @Route("/incident/{id}", name="ims_incident_one", requirements={"incidentId"="\d+"}, methods={"GET"})
     * @param int $id
     * @param IncidentLoader $incidentLoader
     * @return JsonResponse
     */
    public function getIncident(int $id, IncidentLoader $incidentLoader): JsonResponse
    {
        $dto = $incidentLoader->getById($id, $this->security->getUser());

        return ResponseFactory::success((array)$dto);
    }

    /**
     * Поиск инцидентов
     *
     * @Route("/incident/search", name="ims_incident_search", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated use GET /incident
     */
    public function searchIncident(Request $request): JsonResponse
    {
        throw new MethodNotImplementedException('see get list');
    }

    /**
     * Получение истории статусов к инциденту
     *
     * @Route("/incident/{incidentId}/history", name="ims_incident_history", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns incident history",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=HistoryDTO::class))
     *     )
     * )
     *
     * @param $incidentId
     * @param GetHistoryIncidentUserAction $userAction
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function getHistory($incidentId, GetHistoryIncidentUserAction $userAction): JsonResponse
    {
        $user = $this->security->getUser();
        $incident = $userAction->getDtoIfCan($incidentId, $user);

        $history = $userAction->execute($incident, $user);

        return ResponseFactory::success($history);
    }
}
