<?php

namespace App\UserActions\IncidentActionTaskContext;

use Symfony\Component\DependencyInjection\ServiceLocator;

class IncidentActionTaskContextUserActionsLocator
{

    /**
     * @var ServiceLocator
     */
    private $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(string $class): IncidentActionTaskContextUserActionInterface
    {
        return $this->locator->get($class);
    }


    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }


    /**
     * @return IncidentActionTaskContextUserActionInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }
}