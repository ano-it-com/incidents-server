<?php

namespace App\Domain\Incident\TypeHandler;

class SecurityTypeHandler extends IncidentTypeHandler
{
    public static function getCode(): string
    {
        return 'security';
    }
}
