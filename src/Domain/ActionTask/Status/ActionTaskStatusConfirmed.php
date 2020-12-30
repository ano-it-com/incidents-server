<?php

namespace App\Domain\ActionTask\Status;

use App\Domain\ActionTask\ActionTaskStatusInterface;

class ActionTaskStatusConfirmed implements ActionTaskStatusInterface
{
    public const CODE = 'confirmed';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Подтвержден';
    }
}
