<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusCorrection implements ActionStatusInterface
{
    public const CODE = 'correction';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Возвращено на доработку';
    }

    public static function getTTl(): int
    {
        return 60 * 60;
    }
}
