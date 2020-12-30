<?php

namespace App\Domain\ActionTask\Status;

use App\Domain\ActionTask\ActionTaskStatusInterface;

class ActionTaskStatusDone implements ActionTaskStatusInterface
{
    public const CODE = 'done';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Выполнена';
    }
}
