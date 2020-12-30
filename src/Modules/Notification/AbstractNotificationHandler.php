<?php

namespace App\Modules\Notification;

use App\Messenger\Message\TelegramMessage;

abstract class AbstractNotificationHandler implements NotificationHandlerInterface
{
    protected function getTelegramChannel(string $message, array $context, array $users): TelegramMessage
    {
        $userTelegramIds = [];
        foreach ($users as $user) {
            if (null === $user->getTelegramId()) {
                continue;
            }

            $userTelegramIds[] = $user->getTelegramId();
        }

        return new TelegramMessage($message, $context, array_unique($userTelegramIds));
    }
}
