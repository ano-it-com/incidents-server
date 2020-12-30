<?php

namespace App\Domain\ActionTask\Status;

use App\Domain\ActionTask\ActionTaskStatusInterface;

class ActionTaskStatusNotDone implements ActionTaskStatusInterface
{
    public const CODE = 'not_done';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Не выполнена';
    }
}
