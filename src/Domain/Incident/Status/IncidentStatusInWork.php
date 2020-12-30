<?php

namespace App\Domain\Incident\Status;

use App\Domain\Incident\IncidentStatusInterface;

class IncidentStatusInWork implements IncidentStatusInterface
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

    public static function getTtl(): int
    {
        return 60 * 60;
    }
}
