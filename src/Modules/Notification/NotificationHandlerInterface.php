<?php

namespace App\Modules\Notification;

use App\Modules\Notification\Messenger\NotificationMessageInterface;

interface NotificationHandlerInterface
{
    public static function supports(string $eventName): bool;

    public function handle(NotificationMessageInterface $message): array;
}
