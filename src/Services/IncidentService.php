<?php

namespace App\Services;

use App\Controller\Api\Request\Incident\ActionWithCommentDTO;
use App\Controller\Api\Request\Incident\CreateActionByTemplateDTO;
use App\Controller\Api\Request\Incident\CreateActionDTO;
use App\Controller\Api\Request\Incident\CreateActionsByTemplateDTO;
use App\Controller\Api\Request\Incident\CreateActionTaskDTO;
use App\Controller\Api\Request\Incident\CreateIncidentDTO;
use App\Domain\ActionTask\ActionTaskTypeHandlerLocator;
use App\Domain\ActionTask\Status\ActionTaskStatusEmpty;
use App\Domain\ActionTask\ActionTaskStatusLocator;
use App\Domain\Action\ActionStatusLocator;
use App\Domain\Action\Status\ActionStatusNew;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Domain\Incident\IncidentStatusLocator;
use App\Domain\Incident\Status\IncidentStatusNew;
use App\Domain\Incident\IncidentTypeHandlerLocator;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionStatus;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Incident\Action\ActionTaskStatus;
use App\Entity\Incident\Comment\Comment;
use App\Entity\Incident\Incident;
use App\Entity\Incident\IncidentStatus;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Infrastructure\Exceptions\ValidationException;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\Repository\Incident\Action\ActionTaskRepository;
use App\Repository\Incident\Action\ActionTaskTypeRepository;
use App\Repository\Incident\Action\ActionTypeRepository;
use App\Repository\Incident\ActionsTemplate\ActionsTemplateRepository;
use App\Repository\Incident\IncidentRepository;
use App\Repository\Incident\IncidentTypeRepository;
use App\Repository\Security\GroupRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class IncidentService
{
    private Security $security;

    private EntityManagerInterface $em;

    private ActionStatusLocator $actionStatusLocator;

    private ActionTaskStatusLocator $actionTaskStatusLocator;

    private ActionTypeRepository $actionTypeRepository;

    private ActionTaskTypeRepository $actionTaskTypeRepository;

    private IncidentStatusLocator $incidentStatusLocator;

    private FileService $fileService;

    private IncidentRepository $incidentRepository;

    private IncidentTypeRepository $incidentTypeRepository;

    private ActionsTemplateRepository $actionsTemplateRepository;

    private ActionTaskRepository $actionTaskRepository;

    private IncidentTypesService $incidentTypesService;

    private IncidentTypeHandlerLocator $incidentTypeHandlerLocator;

    private ValidatorInterface $validator;

    private ActionTaskTypeHandlerLocator $actionTaskTypeHandlerLocator;

    private GroupRepository $groupRepository;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        FileService $fileService,
        IncidentTypesService $incidentTypesService,
        IncidentTypeRepository $incidentTypeRepository,
        ActionTypeRepository $actionTypeRepository,
        ActionTaskTypeRepository $actionTaskTypeRepository,
        IncidentStatusLocator $incidentStatusLocator,
        ActionStatusLocator $actionStatusLocator,
        ActionTaskStatusLocator $actionTaskStatusLocator,
        ActionsTemplateRepository $actionsTemplateRepository,
        ActionTaskRepository $actionTaskRepository,
        IncidentRepository $incidentRepository,
        IncidentTypeHandlerLocator $incidentTypeHandlerLocator,
        ActionTaskTypeHandlerLocator $actionTaskTypeHandlerLocator,
        ValidatorInterface $validator,
        GroupRepository $groupRepository
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->actionStatusLocator = $actionStatusLocator;
        $this->actionTaskStatusLocator = $actionTaskStatusLocator;
        $this->actionTypeRepository = $actionTypeRepository;
        $this->actionTaskTypeRepository = $actionTaskTypeRepository;
        $this->incidentStatusLocator = $incidentStatusLocator;
        $this->fileService = $fileService;
        $this->incidentTypeRepository = $incidentTypeRepository;
        $this->actionsTemplateRepository = $actionsTemplateRepository;
        $this->actionTaskRepository = $actionTaskRepository;
        $this->incidentTypesService = $incidentTypesService;
        $this->incidentRepository = $incidentRepository;
        $this->incidentTypeHandlerLocator = $incidentTypeHandlerLocator;
        $this->validator = $validator;
        $this->actionTaskTypeHandlerLocator = $actionTaskTypeHandlerLocator;
        $this->groupRepository = $groupRepository;
    }

    public function create(CreateIncidentDTO $createIncidentDTO, User $user): Incident
    {
        $type = $this->incidentTypeRepository->find($createIncidentDTO->typeId);
        if (null === $type) {
            throw new ValidationException(['action' => 'Указан неверный тип инцидента: ' . $createIncidentDTO->typeId]);
        }

        $this->validateIncident($createIncidentDTO, $type);

        $this->em->beginTransaction();
        try {
            $incident = new Incident();
            $incident
                ->setTitle($createIncidentDTO->title)
                ->setDescription($createIncidentDTO->description)
                ->setDate(new DateTimeImmutable())
                ->setCreatedAt(new DateTimeImmutable())
                ->setCreatedBy($user)
                ->setUpdatedAt(new DateTimeImmutable())
                ->setUpdatedBy($user)
                ->setDeleted(false)
                ->setInfo($createIncidentDTO->info)
                ->setType($type);

            $this->em->persist($incident);
            $incident->setStatus($this->createIncidentStatus($incident, IncidentStatusNew::getCode(), $user));

            foreach ($createIncidentDTO->responsibleGroups as $responsibleGroupId) {
                /** @var Group $responsibleGroupReference */
                $responsibleGroupReference = $this->em->getReference(Group::class, $responsibleGroupId);
                $incident->addResponsibleGroup($responsibleGroupReference);
            }

            if ($createIncidentDTO->repeatedIncidentId) {
                /** @var Incident $repeatedIncidentReference */
                $repeatedIncidentReference = $this->em->getReference(Incident::class, $createIncidentDTO->repeatedIncidentId);
                $incident->setRepeatedIncident($repeatedIncidentReference);
            }

            if ($createIncidentDTO->files) {
                $this->fileService->attachFilesTo($incident, $createIncidentDTO->files);
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->actions as $actionDTO) {
                foreach ($this->createActions($actionDTO, $incident, $user) as $action) {
                    $incident->addAction($action);
                }
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->templates as $templateDTO) {
                foreach ($this->createActionsByTemplate($templateDTO, $incident, $user) as $action) {
                    $incident->addAction($action);
                }
            }

            if (count($createIncidentDTO->templates)) {
                // Ставим статус в работе, так как действия сразу в работу уходят
                $incident->setStatus($this->createIncidentStatus($incident, IncidentStatusInWork::getCode(), $user));
            }

            $this->em->flush();

            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $incident;
    }

    public function update(Incident $incident, CreateIncidentDTO $createIncidentDTO, User $user): Incident
    {
        $type = $this->incidentTypeRepository->find($createIncidentDTO->typeId);
        if (null === $type) {
            throw new ValidationException(['action' => 'Указан неверный тип инцидента: ' . $createIncidentDTO->typeId]);
        }

        $this->validateIncident($createIncidentDTO, $type);

        $this->em->beginTransaction();
        try {
            $incident
                ->setTitle($createIncidentDTO->title)
                ->setDescription($createIncidentDTO->description)
                ->setUpdatedAt(new DateTimeImmutable())
                ->setUpdatedBy($user)
                ->setInfo($createIncidentDTO->info)
                ->setType($type);

            $this->em->persist($incident);

            foreach ($incident->getResponsibleGroups() as $responsibleGroup) {
                $incident->removeResponsibleGroup($responsibleGroup);
            }

            foreach ($createIncidentDTO->responsibleGroups as $responsibleGroupId) {
                /** @var Group $responsibleGroupReference */
                $responsibleGroupReference = $this->em->getReference(Group::class, $responsibleGroupId);
                $incident->addResponsibleGroup($responsibleGroupReference);
            }

            if ($createIncidentDTO->repeatedIncidentId) {
                /** @var Incident $repeatedIncidentReference */
                $repeatedIncidentReference = $this->em->getReference(Incident::class, $createIncidentDTO->repeatedIncidentId);
                $incident->setRepeatedIncident($repeatedIncidentReference);
            }

            if ($createIncidentDTO->files) {
                $this->fileService->attachFilesTo($incident, $createIncidentDTO->files);
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->actions as $actionDTO) {
                foreach ($this->createActions($actionDTO, $incident, $user) as $action) {
                    $incident->addAction($action);
                }
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->templates as $templateDTO) {
                foreach ($this->createActionsByTemplate($templateDTO, $incident, $user) as $action) {
                    $incident->addAction($action);
                }
            }

            if (count($createIncidentDTO->templates)) {
                // тсавим статус в работе, так как действия сразу в работу уходят
                $incident->setStatus($this->createIncidentStatus($incident, IncidentStatusInWork::getCode(), $user));
            }

            $this->em->flush();
            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $incident;
    }

    public function createIncidentStatus(Incident $incident, string $statusCode, User $user): IncidentStatus
    {
        if (!$this->incidentStatusLocator->hasByCode($statusCode)) {
            throw new ValidationException(['action' => 'Указан неверный тип статуса инцидента: ' . $statusCode]);
        }

        $status = new IncidentStatus();
        $status
            ->setCode($statusCode)
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setIncident($incident);

        $this->em->persist($status);

        return $status;
    }

    protected function validateIncident(CreateIncidentDTO $createIncidentDTO, IncidentType $type): void
    {
        if(!$this->incidentTypeHandlerLocator->hasByCode($type->getHandler())){
            throw new ValidationException(['info' => 'Указан несуществующий обработчик типа: ' . $type->getHandler()]);
        }

        $typeHandler = $this->incidentTypeHandlerLocator->getByCode($type->getHandler())->loadProperties($createIncidentDTO->info);
        $errors = $this->validator->validate($typeHandler);
        if ($errors->count()) {
            throw ValidationException::fromConstraintViolationList($errors);
        }
    }

    /**
     * @param CreateActionDTO $actionDTO
     * @param Incident $incident
     * @param User|null $user
     * @return Action[]
     */
    public function createActions(CreateActionDTO $actionDTO, Incident $incident, User $user = null): array
    {
        if (!($actionType = $this->actionTypeRepository->find($actionDTO->typeId))) {
            throw new ValidationException(['action' => 'Указан неверный тип действия: ' . $actionDTO->typeId]);
        }

        if (!$this->incidentTypesService->isValidActionTypeForIncidentType($actionType, $incident->getType())) {
            throw new ValidationException(['action' => 'Неверный тип действия для текущего инцидента: ' . $actionDTO->typeId]);
        }

        $actions = [];
        foreach ($actionDTO->responsibleGroup as $groupId) {
            $responsibleGroup = $this->groupRepository->find($groupId);

            $action = new Action();
            $action
                ->setType($actionType)
                ->setCreatedAt(new DateTimeImmutable())
                ->setCreatedBy($user)
                ->setUpdatedAt(new DateTimeImmutable())
                ->setUpdatedBy($user)
                ->setResponsibleUser(null)
                ->setDeleted(false)
                ->setIncident($incident)
                ->setResponsibleGroup($responsibleGroup);

            $this->em->persist($action);
            $action->setStatus($this->createActionStatus($action, ActionStatusNew::getCode(), $user, $responsibleGroup));

            if ($actionDTO->files) {
                $this->fileService->attachFilesWithCopyTo($action, $actionDTO->files);
            }

            /** @var CreateActionTaskDTO $taskDTO */
            foreach ($actionDTO->tasks as $taskDTO) {
                $action->addActionTask($this->createActionTask($taskDTO, $action, $user));
            }

            $actions[] = $action;
        }

        return $actions;
    }

    public function createActionStatus(Action $action, string $statusCode, User $user, Group $responsibleGroup, ?User $responsibleUser = null): ActionStatus
    {
        if (!$this->actionStatusLocator->hasByCode($statusCode)) {
            throw new InvalidArgumentException('Action status with code ' . $statusCode . ' not found!');
        }

        $status = new ActionStatus();
        $status
            ->setCode($statusCode)
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setAction($action)
            ->setResponsibleGroup($responsibleGroup)
            ->setResponsibleUser($responsibleUser);

        $this->em->persist($status);

        return $status;
    }

    public function createActionTask(CreateActionTaskDTO $actionTaskDTO, Action $action, User $user): ActionTask
    {
        if (!($actionTaskType = $this->actionTaskTypeRepository->find($actionTaskDTO->typeId))) {
            throw new ValidationException(['actionTaskType' => 'Неверный тип рекомендации для текущего действия: ' . $actionTaskDTO->typeId]);
        }

        if (!$this->incidentTypesService->isValidTaskTypeForActionType($actionTaskType, $action->getType())) {
            throw new ValidationException(['actionTask' => 'Неверный тип рекомендации для текущего действия: ' . $actionTaskDTO->typeId]);
        }

        $typeHandler = $this->actionTaskTypeHandlerLocator->getByCode($actionTaskType->getHandler());
        foreach ([$actionTaskDTO->inputData, $actionTaskDTO->reportData] as $data) {
            $typeHandlerData = $typeHandler->loadProperties($data);

            $errors = $this->validator->validate($typeHandlerData);
            if ($errors->count()) {
                throw ValidationException::fromConstraintViolationList($errors);
            }
        }

        $actionTask = new ActionTask();
        $actionTask
            ->setType($actionTaskType)
            ->setAction($action)
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUpdatedBy($user)
            ->setDeleted(false)
            ->setInputData($actionTaskDTO->inputData)
            ->setReportData($actionTaskDTO->reportData);

        $this->em->persist($actionTask);
        $actionTask->setStatus($this->createActionTaskStatus($actionTask, ActionTaskStatusEmpty::getCode(), $user));

        if ($actionTaskDTO->filesInput) {
            $this->fileService->attachFilesWithCopyTo($actionTask, $actionTaskDTO->filesInput, $actionTask::INPUT_FILES_OWNER_CODE);
        }

        if ($actionTaskDTO->filesReport) {
            $this->fileService->attachFilesWithCopyTo($actionTask, $actionTaskDTO->filesReport, $actionTask::REPORT_FILES_OWNER_CODE);
        }

        return $actionTask;
    }

    public function createActionTaskStatus(ActionTask $actionTask, string $statusCode, User $user): ActionTaskStatus
    {
        if (!$this->actionTaskStatusLocator->hasByCode($statusCode)) {
            throw new ValidationException(['statusCode' => 'Неверный статус задачи: ' . $statusCode]);
        }

        $actionTaskStatus = new ActionTaskStatus();
        $actionTaskStatus
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setActionTask($actionTask)
            ->setCode($statusCode);

        $this->em->persist($actionTaskStatus);

        return $actionTaskStatus;
    }

    /**
     * @param CreateActionsByTemplateDTO $templateDTO
     * @param Incident $incident
     * @param User $user
     * @return Action[]
     */
    public function createActionsByTemplate(CreateActionsByTemplateDTO $templateDTO, Incident $incident, User $user): array
    {
        $template = $this->actionsTemplateRepository->find($templateDTO->templateId);
        if (!$template) {
            throw new ValidationException(['templateId' => 'Шаблон не найден: ' . $templateDTO->templateId]);
        }

        $actionMappingToCreate = $template->getActionsMapping();
        if (!count($templateDTO->actions)) {
            return [];
        }

        $selectedActionsIds = array_map(
            function (CreateActionByTemplateDTO $action) {
                return $action->actionTypeId;
            },
            $templateDTO->actions
        );

        $actionTypeIdToResponsibleGroupId = [];
        /** @var CreateActionByTemplateDTO $action */
        foreach ($templateDTO->actions as $action) {
            $actionTypeIdToResponsibleGroupId[$action->actionTypeId] = $action->groupId;
        }

        $allowedActionsIds = $this->incidentTypesService->getActionTypeIdsForIncidentType($incident->getType()->getId());
        // создаем только выбранные
        $actionMappingToCreate = array_filter($actionMappingToCreate, function ($mapping) use ($selectedActionsIds, $allowedActionsIds) {
            $actionTypeId = $mapping['actionTypeId'];
            return in_array($actionTypeId, $selectedActionsIds) &&
                in_array($actionTypeId, $allowedActionsIds);
        });

        $allActions = [];
        foreach ($actionMappingToCreate as $mapping) {
            $actionTypeId = $mapping['actionTypeId'];
            $groupId = $actionTypeIdToResponsibleGroupId[$actionTypeId] ?? null;

            // создаем действия сами
            $actionDTO = new CreateActionDTO();
            $actionDTO->typeId = $actionTypeId;
            $actionDTO->responsibleGroup = [$groupId];

            $taskTypeIds = $this->incidentTypesService->getTaskTypeIdsForActionType($actionTypeId);
            if (count($taskTypeIds) == 0) {
                throw new ValidationException(['actionTypeId' => "Для действия $actionTypeId не найден тип рекомендации"]);
            }

            $actionDTO->tasks = [];
            foreach ($taskTypeIds as $taskTypeId){
                $task = new CreateActionTaskDTO();
                $task->typeId = $taskTypeId;
                $actionDTO->tasks[] = $task;
            }

            $actions = $this->createActions($actionDTO, $incident, $user);
            foreach ($actions as $action) {
                $action->setTemplateId($templateDTO->templateId);
                $allActions[] = $action;
            }
        }

        return $allActions;
    }

    public function createActionComment(IncidentDTO $incidentDTO, ActionDTO $actionDTO, ActionWithCommentDTO $actionWithCommentDTO, User $user, Group $targetGroup): Comment
    {
        $comment = new Comment();
        $comment
            ->setText($actionWithCommentDTO->comment)
            ->setIncident($this->em->getReference(Incident::class, $incidentDTO->id))
            ->setAction($this->em->getReference(Action::class, $actionDTO->id))
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUpdatedBy($user)
            ->setTargetGroup($targetGroup)
            ->setDeleted(false);

        $this->em->persist($comment);
        $this->em->flush();

        $fileIds = $actionWithCommentDTO->fileIds;
        if (count($fileIds)) {
            $this->fileService->attachFilesTo($comment, $fileIds);
        }

        return $comment;
    }
}
