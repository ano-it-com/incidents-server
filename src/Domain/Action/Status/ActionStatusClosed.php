<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusClosed implements ActionStatusInterface
{
    public const CODE = 'closed';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Закрыто';
    }

    public static function getTTl(): int
    {
        return 0;
    }
}
