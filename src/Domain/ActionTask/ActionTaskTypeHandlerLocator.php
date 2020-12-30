<?php

namespace App\Domain\ActionTask;

use Symfony\Component\DependencyInjection\ServiceLocator;

class ActionTaskTypeHandlerLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getByCode(string $code): ActionTaskTypeHandlerInterface
    {
        return $this->locator->get($code);
    }

    /**
     * @return ActionTaskTypeHandlerInterface[]
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