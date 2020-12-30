<?php

namespace App\Domain\ActionTask\Status;

use App\Domain\ActionTask\ActionTaskStatusInterface;

class ActionTaskStatusEmpty implements ActionTaskStatusInterface
{
    public const CODE = 'empty';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return '-';
    }
}
