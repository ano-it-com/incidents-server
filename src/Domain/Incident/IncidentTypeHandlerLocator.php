<?php

namespace App\Domain\Incident;

use Symfony\Component\DependencyInjection\ServiceLocator;

class IncidentTypeHandlerLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getByCode(string $code): IncidentTypeHandlerInterface
    {
        return $this->locator->get($code);
    }

    /**
     * @return IncidentTypeHandlerInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }

    public function hasByCode(string $code): bool
    {
        return $this->locator->has($code);
    }
}