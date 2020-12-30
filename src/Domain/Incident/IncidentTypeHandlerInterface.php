<?php

namespace App\Domain\Incident;

use Doctrine\Common\Collections\ArrayCollection;

interface IncidentTypeHandlerInterface
{
    public static function getCode(): string;

    public function loadProperties(array $data);

    public function getProperties($initPrepared): ArrayCollection;
}
