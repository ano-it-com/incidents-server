<?php

namespace App\UserActions\IncidentActionTaskContext\Event;

use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Incident\Incident;
use App\UserActions\IncidentActionTaskContext\IncidentActionTaskContextUserActionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ActionTaskContextEvent extends Event
{
    private IncidentActionTaskContextUserActionInterface $userAction;

    protected Incident $incident;

    protected Action $action;

    protected ActionTask $actionTask;

    protected UserInterface $user;

    public function __construct(
        IncidentActionTaskContextUserActionInterface $userAction,
        Incident $incident,
        Action $action,
        ActionTask $actionTask,
        UserInterface $user
    ) {
        $this->incident = $incident;
        $this->action = $action;
        $this->user = $user;
        $this->actionTask = $actionTask;
        $this->userAction = $userAction;
    }

    public function getUserAction(): IncidentActionTaskContextUserActionInterface
    {
        return $this->userAction;
    }

    public function getIncident(): Incident
    {
        return $this->incident;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getActionTask(): ActionTask
    {
        return $this->actionTask;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
