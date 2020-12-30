<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusClarification implements ActionStatusInterface
{
    public const CODE = 'clarification';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'На уточнении';
    }

    public static function getTTl(): int
    {
        return 60 * 60;
    }
}
