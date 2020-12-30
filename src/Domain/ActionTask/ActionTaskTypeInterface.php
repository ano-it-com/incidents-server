<?php

namespace App\Domain\ActionTask;

interface ActionTaskTypeInterface
{
    public static function getCode(): string;

    public static function getTitle(): string;

    /**
     * @return string[]
     */
    public static function getAllowedStatusCodes(): array;
}
