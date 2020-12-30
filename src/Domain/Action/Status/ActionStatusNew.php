<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusNew implements ActionStatusInterface
{
    public const CODE = 'new';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Новое';
    }

    public static function getTTl(): int
    {
        return 60 * 60;
    }
}
