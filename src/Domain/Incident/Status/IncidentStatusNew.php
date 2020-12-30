<?php

namespace App\Domain\Incident\Status;

use App\Domain\Incident\IncidentStatusInterface;

class IncidentStatusNew implements IncidentStatusInterface
{
    public const CODE = 'new';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public static function getTitle(): string
    {
        return 'Новый';
    }

    public static function getTtl(): int
    {
        return 60 * 60;
    }
}
