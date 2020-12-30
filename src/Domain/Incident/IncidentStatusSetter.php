<?php

namespace App\Domain\Incident;

use App\Domain\Action\ActionStatusLocator;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\ActionStatusDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentStatusDTO;

class IncidentStatusSetter
{
    private IncidentStatusLocator $incidentStatusLocator;

    private ActionStatusLocator $actionStatusLocator;

    public function __construct(IncidentStatusLocator $incidentStatusLocator, ActionStatusLocator $actionStatusLocator)
    {
        $this->incidentStatusLocator = $incidentStatusLocator;
        $this->actionStatusLocator = $actionStatusLocator;
    }

    /**
     * @param IncidentDTO[] $dtos
     */
    public function setStatusesToDTOs(array $dtos): void
    {
        foreach ($dtos as $dto){
            /** @var IncidentStatusDTO $incidentStatus */
            foreach ($dto->statuses as $incidentStatus) {
                $incidentStatusClass = $this->incidentStatusLocator->getByCode($incidentStatus->code);
                $incidentStatus->ttl = $incidentStatusClass::getTtl();
                $incidentStatus->title = $incidentStatusClass::getTitle();
            }

            /** @var ActionDTO $action */
            foreach ($dto->actions as $action){
                /** @var ActionStatusDTO $actionStatus */
                foreach ($action->statuses as $actionStatus) {
                    $actionStatusClass = $this->actionStatusLocator->getByCode($actionStatus->code);
                    $actionStatus->ttl = $actionStatusClass::getTTl();
                    $actionStatus->title = $actionStatusClass::getTitle();
                }
            }
        }
    }
}
