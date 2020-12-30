<?php


namespace TelegramNotificationBundle\Services;


class TelegramRawMessage implements TelegramMessageInterface
{
    protected $rawMessage;

    protected $parseMode;

    public function __construct($rawMessage, $parseMode = 'html')
    {
        $this->rawMessage = $rawMessage;
        $this->parseMode = $parseMode;
    }

    public function render()
    {
        return $this->rawMessage;
    }

    public function getParseMode()
    {
        return $this->parseMode;
    }
}