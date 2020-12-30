<?php

namespace App\UserActions\IncidentContext\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramNotificationBundle\Services\Client\TelegramClientInterface;

class IncidentTelegramNotificationSubscriber implements EventSubscriberInterface
{
    private TelegramClientInterface $telegramClient;

    public function __construct(TelegramClientInterface $telegramClient)
    {
        $this->telegramClient = $telegramClient;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
