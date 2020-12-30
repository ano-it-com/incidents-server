<?php

namespace App\Domain\ActionTask\Type;

use App\Domain\ActionTask\ActionTaskTypeInterface;
use App\Domain\ActionTask\Status\ActionTaskStatusDone;
use App\Domain\ActionTask\Status\ActionTaskStatusEmpty;
use App\Domain\ActionTask\Status\ActionTaskStatusNotDone;

class ActionTaskTypeDetail implements ActionTaskTypeInterface
{
    public static function getCode(): string
    {
        return 'detail_report';
    }

    public static function getTitle(): string
    {
        return 'Расширенная рекомендация';
    }

    public static function getAllowedStatusCodes(): array
    {
        return [
            ActionTaskStatusEmpty::getCode(),
            ActionTaskStatusDone::getCode(),
            ActionTaskStatusNotDone::getCode()
        ];
    }
}
