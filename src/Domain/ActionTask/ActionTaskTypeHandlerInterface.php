<?php

namespace App\Domain\ActionTask;

use Doctrine\Common\Collections\ArrayCollection;

interface ActionTaskTypeHandlerInterface
{
    public static function getCode(): string;

    public function loadProperties(array $data);

    public function getProperties($initPrepared): ArrayCollection;
}
