<?php

namespace App\Modules\Export\EAV;

use Symfony\Component\DependencyInjection\ServiceLocator;

class EAVExportersLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function get(string $class): EAVExporterInterface
    {
        return $this->locator->get($class);
    }

    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }

    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }
}
