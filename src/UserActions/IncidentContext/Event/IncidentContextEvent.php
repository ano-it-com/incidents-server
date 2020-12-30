<?php

namespace App\UserActions\IncidentContext\Event;

use App\Entity\Incident\Incident;
use App\UserActions\IncidentContext\IncidentContextUserActionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class IncidentContextEvent extends Event
{
    private IncidentContextUserActionInterface $userAction;

    protected Incident $incident;

    protected UserInterface $user;

    private ?array $context;

    public function __construct(
        IncidentContextUserActionInterface $userAction,
        Incident $order,
        UserInterface $user,
        ?array $context = null
    ) {
        $this->userAction = $userAction;
        $this->incident = $order;
        $this->user = $user;
        $this->context = $context;
    }

    public function getUserAction(): IncidentContextUserActionInterface
    {
        return $this->userAction;
    }

    public function getIncident(): Incident
    {
        return $this->incident;
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