<?php

namespace App\Messenger\Message;

use App\Messenger\TelegramMessageInterface;

class TelegramMessage implements TelegramMessageInterface
{
    private string $message;

    private array $context;

    private array $telegramIds;

    public function __construct(string $message, array $context, array $telegramIds)
    {
        $this->message = $message;
        $this->context = $context;
        $this->telegramIds = $telegramIds;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getTelegramIds(): array
    {
        return $this->telegramIds;
    }
}