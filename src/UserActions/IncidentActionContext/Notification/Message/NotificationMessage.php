<?php

namespace App\UserActions\IncidentActionContext\Notification\Message;

use App\Modules\Notification\Messenger\NotificationMessageInterface;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationMessage implements NotificationMessageInterface
{
    private string $eventName;

    protected IncidentDTO $incident;

    protected ActionDTO $action;

    protected UserInterface $user;

    private ?array $context;

    public function __construct(
        string $eventName,
        IncidentDTO $incident,
        ActionDTO $action,
        UserInterface $user,
        ?array $context
    ) {
        $this->eventName = $eventName;
        $this->incident = $incident;
        $this->action = $action;
        $this->user = $user;
        $this->context = $context;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getIncident(): IncidentDTO
    {
        return $this->incident;
    }

    public function getAction(): ActionDTO
    {
        return $this->action;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
