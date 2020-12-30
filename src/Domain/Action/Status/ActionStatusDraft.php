<?php

namespace App\Domain\Action\Status;

use App\Domain\Action\ActionStatusInterface;

class ActionStatusDraft implements ActionStatusInterface
{
    public const CODE = 'draft';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Черновик';
    }

    public static function getTTl(): int
    {
        return 0;
    }
}

