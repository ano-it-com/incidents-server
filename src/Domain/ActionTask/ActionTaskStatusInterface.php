<?php

namespace App\Domain\ActionTask;

interface ActionTaskStatusInterface
{
    public static function getCode(): string;

    public static function getTitle(): string;
}
