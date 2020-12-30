<?php

namespace TelegramNotificationBundle\Services;

interface TelegramMessageInterface
{
    public function render();

    public function getParseMode();
}