<?php

namespace App\UserActions\IncidentActionContext\EventListener;

use App\UserActions\IncidentActionContext\Event\ActionContextEvent;
use App\UserActions\IncidentActionContext\Notification\Message\NotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActionNotificationSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ActionContextEvent::ACTION_COMPLETE_NOTIFICATION_EVENT  => 'onActionCompleteNotificationEvent',
        ];
    }

    public function onActionCompleteNotificationEvent(ActionContextEvent $event): void
    {
        $this->messageBus->dispatch(
            new NotificationMessage(
                get_class($event->getUserAction()),
                $event->getIncident(),
                $event->getAction(),
                $event->getUser(),
                $event->getContext(),
            ),
        );
    }
}
