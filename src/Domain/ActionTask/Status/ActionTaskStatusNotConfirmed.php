<?php

namespace App\Domain\ActionTask\Status;

use App\Domain\ActionTask\ActionTaskStatusInterface;

class ActionTaskStatusNotConfirmed implements ActionTaskStatusInterface
{
    public const CODE = 'not_confirmed';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Не подтвержден';
    }
}
