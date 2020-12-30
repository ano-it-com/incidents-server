<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusInWork implements ActionStatusInterface
{
    public const CODE = 'in_work';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'В работе';
    }

    public static function getTTl(): int
    {
        return 60 * 60;
    }
}
