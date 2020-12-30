<?php

namespace App\Domain\Action;

interface ActionStatusInterface
{
    public static function getCode(): string;

    public static function getTitle(): string;

    public static function getTTl(): int;
}
