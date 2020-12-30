<?php

namespace App\UserActions\IncidentActionContext\Event;

use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\UserActions\IncidentActionContext\IncidentActionContextUserActionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ActionContextEvent extends Event
{
    public const ACTION_COMPLETE_NOTIFICATION_EVENT = 'actionCompleteNotificationEvent';

    private IncidentActionContextUserActionInterface $userAction;

    protected IncidentDTO $incident;

    protected ActionDTO $action;

    protected UserInterface $user;

    private ?array $context;

    public function __construct(
        IncidentActionContextUserActionInterface $userAction,
        IncidentDTO $order,
        ActionDTO $action,
        UserInterface $user,
        ?array $context = null
    ) {
        $this->userAction = $userAction;
        $this->incident = $order;
        $this->action = $action;
        $this->user = $user;
        $this->context = $context;
    }

    public function getUserAction(): IncidentActionContextUserActionInterface
    {
        return $this->userAction;
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