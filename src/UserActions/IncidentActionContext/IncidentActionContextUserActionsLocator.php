<?php

namespace App\UserActions\IncidentActionContext;

use Symfony\Component\DependencyInjection\ServiceLocator;

class IncidentActionContextUserActionsLocator
{

    /**
     * @var ServiceLocator
     */
    private $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(string $class): IncidentActionContextUserActionInterface
    {
        return $this->locator->get($class);
    }


    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }


    /**
     * @return IncidentActionContextUserActionInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }
}