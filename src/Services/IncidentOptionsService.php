<?php

namespace App\Services;

use App\Domain\ActionTask\ActionTaskStatusInterface;
use App\Domain\ActionTask\ActionTaskStatusLocator;
use App\Domain\Action\ActionStatusInterface;
use App\Domain\Action\ActionStatusLocator;
use App\Domain\ActionTask\ActionTaskTypeHandlerLocator;
use App\Domain\Incident\IncidentStatusInterface;
use App\Domain\Incident\IncidentStatusLocator;
use App\Domain\Incident\IncidentTypeHandlerLocator;
use App\Domain\CommonPropertyHandler;
use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\ReadModel\Loaders\Incident\Criteria\UserPermissionsCriteria;
use App\Repository\Incident\Action\ActionTypeRepository;
use App\Services\Providers\GroupProvider;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class IncidentOptionsService
{
    /** @var ActionTypeRepository */
    private $em;

    private GroupProvider $groupProvider;

    private ActionTaskStatusLocator $actionTaskStatusLocator;

    private ActionStatusLocator $actionStatusLocator;

    private IncidentStatusLocator $incidentStatusLocator;

    private UserPermissionsCriteria $permissionsCriteria;

    private IncidentTypeHandlerLocator $incidentTypeHandlerLocator;

    private ActionTaskTypeHandlerLocator $actionTaskTypeHandlerLocator;

    public function __construct(
        EntityManagerInterface $em,
        GroupProvider $groupProvider,
        ActionTaskStatusLocator $actionTaskStatusLocator,
        IncidentStatusLocator $incidentStatusLocator,
        ActionStatusLocator $actionStatusLocator,
        UserPermissionsCriteria $permissionsCriteria,
        IncidentTypeHandlerLocator $incidentTypeHandlerLocator,
        ActionTaskTypeHandlerLocator $actionTaskTypeHandlerLocator
    ) {
        $this->em = $em;
        $this->groupProvider = $groupProvider;
        $this->actionTaskStatusLocator = $actionTaskStatusLocator;
        $this->incidentStatusLocator = $incidentStatusLocator;
        $this->actionStatusLocator = $actionStatusLocator;
        $this->permissionsCriteria = $permissionsCriteria;
        $this->incidentTypeHandlerLocator = $incidentTypeHandlerLocator;
        $this->actionTaskTypeHandlerLocator = $actionTaskTypeHandlerLocator;
    }

    public function getOptions(User $user = null): array
    {
        return [
            'groups' => $this->getGroups(),
            'types' => $this->getTypes(),
            'handlers' => $this->getHandlers('incident'),
            'actionTaskHandlers' => $this->getHandlers('actionTask'),
            'templates' => $this->getTemplates(),
            'filters' => $this->getFilters($user),
            'statuses' => [
                'incident' => $this->getIncidentStatuses(),
                'action' => $this->getActionStatuses(),
                'task' => $this->getTaskStatuses(),
            ]
        ];
    }

    public function getHandlers(string $domain): array
    {
        $handlerLocators = [
            'incident' => $this->incidentTypeHandlerLocator,
            'actionTask' => $this->actionTaskTypeHandlerLocator,
        ];

        if (!isset($handlerLocators[$domain])) {
            return [];
        }

        $handlerLocator = $handlerLocators[$domain];

        return array_map(fn ($class) => [
            'id' => $class::getCode(),
            'properties' => array_map(fn (CommonPropertyHandler $property) => [
                'id' => $property->getId(),
                'name' => $property->getName(),
                'type' => $property->getType(),
                'prepared' => $property->getPrepared(),
            ], array_values($handlerLocator->getByCode($class::getCode())->getProperties()->toArray()))
        ], $handlerLocator->getAllClasses());
    }

    public function getFilters(User $user = null): array
    {
        //TODO переписать после появления нового incidentProvider
        $qb = $this->em->getConnection()
            ->createQueryBuilder()
            ->from('incidents', 'incidents')
            ->andWhere('incidents.deleted <> true');
        if ($user) {
            $this->permissionsCriteria->applyToIncidentsQuery($qb, $user);
        }
        return [
            'status_code' => $this->getFilterStatusCodes($qb),
            'created_by_id' => $this->getFilterCreatedBy($qb),
        ];
    }

    protected function getFilterStatusCodes(QueryBuilder $qb): array
    {
        $cloned = clone $qb;
        $codes = $cloned->select('incident_statuses.code')->distinct()
            ->leftJoin('incidents', 'incident_statuses', 'incident_statuses', 'incident_statuses.incident_id = incidents.id')
            ->orderBy('incident_statuses.code', 'asc')
            ->execute()
            ->fetchAllAssociative();

        return array_map(function ($code) {
            /** @var IncidentStatusInterface $statusClass */
            $statusClass = $this->incidentStatusLocator->getClassByCode($code);

            return [
                'id' => $code,
                'title' => $statusClass::getTitle()
            ];

        }, array_column($codes, 'code'));
    }

    protected function getFilterCreatedBy(QueryBuilder $qb): array
    {
        $cloned = clone $qb;
        $users = $cloned->select('users.id', 'users.last_name', 'users.first_name')->distinct()
            ->leftJoin('incidents', 'users', 'users', 'users.id = incidents.created_by_id')
            ->orderBy('users.last_name', 'asc')
            ->execute()
            ->fetchAllAssociative();

        return array_map(static function (array $user) {

            return [
                'id' => $user['id'],
                'title' => $user['last_name'] . ' ' . $user['first_name'],
            ];

        }, $users);
    }

    public function getGroups(): array
    {
        return array_map(function (Group $group) {
            return [
                'id' => $group->getId(),
                'title' => $group->getTitle(),
                'code' => $group->getCode(),
            ];
        }, $this->groupProvider->getAllCanBeResponsibleForAction());
    }

    public function getTypes(): array
    {
        $incidentTypes = $this->em->getRepository(IncidentType::class)->findBy(['deleted' => false], ['title' => 'asc']);

        return array_map(function (IncidentType $incidentType) {
            return [
                'id' => $incidentType->getId(),
                'title' => $incidentType->getTitle(),
                'description' => $incidentType->getDescription(),
                'handler' => $incidentType->getHandler(),
                'action_types' => array_map(function (ActionType $type) {
                    return [
                        'id' => $type->getId(),
                        'title' => $type->getTitle(),
                        'task_types' => array_map(function (ActionTaskType $actionTaskType) {
                            return [
                                'id' => $actionTaskType->getId(),
                                'title' => $actionTaskType->getTitle(),
                            ];
                        }, $type->getActionTaskTypes()->toArray()),
                    ];
                }, $incidentType->getActionTypes()->toArray())
            ];
        }, (array)$incidentTypes);
    }

    public function getTemplates(): array
    {
        /** @var ActionsTemplate[] $templates */
        $templates = $this->em->getRepository(ActionsTemplate::class)->findBy(['deleted' => false], ['sort' => 'asc']);
        /** @var Group[] $groups */
        $groups = $this->getGroups();
        $indexedGroups = array_combine(array_column($groups, 'id'), $groups);

        $incidentTypes = $this->getTypes();
        $indexedIncidentTypes = array_combine(array_column($incidentTypes, 'id'), $incidentTypes);

        $options = [];
        foreach ($templates as $template) {
            $actionTypes = $indexedIncidentTypes[$template->getIncidentType()->getId()]['action_types'] ?? [];
            $indexedActionTypes = array_combine(array_column($actionTypes, 'id'), $actionTypes);
            $option = [
                'id' => $template->getId(),
                'title' => $template->getTitle(),
                'incident_type_id' => $template->getIncidentType()->getId(),
                'actions' => []
            ];

            $actionsMapping = $template->getActionsMapping();
            foreach ($actionsMapping as $mapping) {
                $actionType = $indexedActionTypes[$mapping['actionTypeId']] ?? null;
                $responsibleGroup = $indexedGroups[$mapping['groupId']] ?? null;
                if (!$actionType || !$responsibleGroup) {
                    continue;
                }

                $option['actions'][] = array_merge($actionType, ['responsibleGroup' => $responsibleGroup]);
            }
            if (count($option['actions']) > 0) {
                $options[] = $option;
            }
        }

        return $options;
    }

    public function getIncidentStatuses(): array
    {
        $classes = array_values($this->incidentStatusLocator->getAllClasses());
        usort($classes, function (string $classA, string $classB) {
            /** @var IncidentStatusInterface $classA */
            /** @var IncidentStatusInterface $classB */
            return strcmp($classA::getTitle(), $classB::getTitle());
        });
        return array_map(function (string $taskStatus) {
            return [
                'code' => $taskStatus::getCode(),
                'title' => $taskStatus::getTitle(),
            ];
        }, $classes);
    }

    public function getActionStatuses(): array
    {
        $classes = array_values($this->actionStatusLocator->getAllClasses());
        usort($classes, function (string $classA, string $classB) {
            /** @var ActionStatusInterface $classA */
            /** @var ActionStatusInterface $classB */
            return strcmp($classA::getTitle(), $classB::getTitle());
        });
        return array_map(function (string $taskStatus) {
            return [
                'code' => $taskStatus::getCode(),
                'title' => $taskStatus::getTitle(),
            ];
        }, $classes);
    }

    public function getTaskStatuses(): array
    {
        $classes = array_values($this->actionTaskStatusLocator->getAllClasses());
        usort($classes, function (string $classA, string $classB) {
            /** @var ActionTaskStatusInterface $classA */
            /** @var ActionTaskStatusInterface $classB */
            return strcmp($classA::getTitle(), $classB::getTitle());
        });
        return array_map(function (string $taskStatus) {
            return [
                'code' => $taskStatus::getCode(),
                'title' => $taskStatus::getTitle(),
            ];
        }, $classes);
    }
}