<?php


namespace App\Services;


use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\IncidentType;
use Doctrine\DBAL\Connection;

class IncidentTypesService
{
    /** @var Connection */
    private $connection;

    /** @var array */
    private $cacheTaskTypeForActionType;

    /** @var array */
    private $cacheActionTypeForIncidentType;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    private function getCachedTaskTypeForActionType(): array
    {
        if ($this->cacheTaskTypeForActionType === null) {
            $this->cacheTaskTypeForActionType = [];
            $query = $this->connection->createQueryBuilder();
            $rows = $query->select('action_task_type_id', 'action_type_id')
                ->from('action_type_action_task_types')
                ->execute()
                ->fetchAllAssociative();
            foreach ($rows as $row) {
                $this->cacheTaskTypeForActionType[$row['action_type_id']][] = $row['action_task_type_id'];
            }
        }
        return $this->cacheTaskTypeForActionType;
    }

    private function getCachedActionTypeForIncidentType(): array
    {
        if ($this->cacheActionTypeForIncidentType === null) {
            $this->cacheActionTypeForIncidentType = [];
            $query = $this->connection->createQueryBuilder();
            $rows = $query->select('incident_type_id', 'action_type_id')
                ->from('incident_type_action_types')
                ->execute()
                ->fetchAllAssociative();
            foreach ($rows as $row) {
                $this->cacheActionTypeForIncidentType[$row['incident_type_id']][] = $row['action_type_id'];
            }
        }
        return $this->cacheActionTypeForIncidentType;
    }

    public function getTaskTypeIdsForActionType($actionTypeId): array
    {
        return $this->getCachedTaskTypeForActionType()[$actionTypeId] ?? [];
    }

    public function isValidTaskTypeForActionType(ActionTaskType $actionTaskType, ActionType $actionType): bool
    {
        return in_array($actionTaskType->getId(), $this->getCachedTaskTypeForActionType()[$actionType->getId()] ?? []);
    }

    public function getActionTypeIdsForIncidentType($incidentTypeId): array
    {
        return $this->getCachedActionTypeForIncidentType()[$incidentTypeId] ?? [];
    }

    public function isValidActionTypeForIncidentType(ActionType $actionType, IncidentType $incidentType): bool
    {
        return in_array($actionType->getId(), $this->getCachedActionTypeForIncidentType()[$incidentType->getId()] ?? []);
    }
}