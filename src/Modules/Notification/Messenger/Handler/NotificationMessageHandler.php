<?php

namespace App\Modules\Notification\Messenger\Handler;

use App\Modules\Notification\Messenger\NotificationMessageInterface;
use App\Modules\Notification\NotificationHandlerLocator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationMessageHandler implements MessageHandlerInterface
{
    private NotificationHandlerLocator $notificationHandlerLocator;

    private MessageBusInterface $messageBus;

    public function __construct(NotificationHandlerLocator $notificationHandlerLocator, MessageBusInterface $messageBus)
    {
        $this->notificationHandlerLocator = $notificationHandlerLocator;
        $this->messageBus = $messageBus;
    }

    public function __invoke(NotificationMessageInterface $message)
    {
        foreach ($this->notificationHandlerLocator->getAllClasses() as $notificationHandlerClass) {
            if (!$notificationHandlerClass::supports($message->getEventName())) {
                continue;
            }

            $notificationHandler = $this->notificationHandlerLocator->get($notificationHandlerClass);

            $notifications = $notificationHandler->handle($message);

            foreach ($notifications as $notification) {
                $this->messageBus->dispatch($notification);
            }
        }
    }
}
