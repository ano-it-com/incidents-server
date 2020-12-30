<?php

namespace TelegramNotificationBundle\Services\Wrapper;

use TelegramNotificationBundle\Services\Client\TelegramClientInterface;
use TelegramNotificationBundle\Services\TelegramMessageInterface;
use TelegramNotificationBundle\Services\TelegramRawMessage;

class TelegramSingleChatWrapper implements TelegramClientInterface
{
    /** @var TelegramClientInterface */
    protected $client;

    protected $chatId;

    public function __construct(TelegramClientInterface $client, $chatId)
    {
        $this->client = $client;
        $this->chatId = $chatId;
    }

    public function send(TelegramMessageInterface $message, int $chatId): bool
    {
        $rawMessage = "#######################". PHP_EOL;
        $rawMessage .= "# Сообщение для чата: <b>$chatId</b>" . PHP_EOL ;
        $rawMessage .= "#######################". PHP_EOL;
        $rawMessage .= $message->render();

        return $this->client->send(
            new TelegramRawMessage($rawMessage, $message->getParseMode()),
            $this->chatId
        );
    }
}