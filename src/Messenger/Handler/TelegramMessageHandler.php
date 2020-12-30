<?php

namespace App\Messenger\Handler;

use App\Messenger\Message\TelegramMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use TelegramNotificationBundle\Services\Client\TelegramClientInterface;
use TelegramNotificationBundle\Services\TelegramMessage as TelegramBundleMessage;

class TelegramMessageHandler implements MessageHandlerInterface
{
    private TelegramClientInterface $telegramClient;

    public function __construct(TelegramClientInterface $telegramClient)
    {
        $this->telegramClient = $telegramClient;
    }

    public function __invoke(TelegramMessage $message)
    {
        $telegramMessage = new TelegramBundleMessage($message->getMessage(), $message->getContext());

        foreach (array_unique($message->getTelegramIds()) as $telegramId) {
            $this->telegramClient->send($telegramMessage, $telegramId);
        }
    }
}
