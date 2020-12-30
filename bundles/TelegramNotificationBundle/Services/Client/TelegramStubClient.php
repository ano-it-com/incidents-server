<?php

namespace TelegramNotificationBundle\Services\Client;


use TelegramNotificationBundle\Services\TelegramMessageInterface;

class TelegramStubClient implements TelegramClientInterface
{
    public function send(TelegramMessageInterface $message, int $chatId): bool
    {
        return true;
    }
}