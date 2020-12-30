<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusApproving implements ActionStatusInterface
{
    public const CODE = 'approving';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'На проверке';
    }

    public static function getTTl(): int
    {
        return 60 * 60;
    }
}
