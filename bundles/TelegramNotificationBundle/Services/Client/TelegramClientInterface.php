<?php

namespace TelegramNotificationBundle\Services\Client;


use TelegramNotificationBundle\Services\TelegramMessageInterface;

interface TelegramClientInterface
{
    public function send(TelegramMessageInterface $message, int $chatId): bool;
}