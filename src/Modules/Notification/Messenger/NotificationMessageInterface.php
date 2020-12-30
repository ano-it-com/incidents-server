<?php

namespace App\Modules\Notification\Messenger;

interface NotificationMessageInterface
{
    public function getEventName(): string;
}
