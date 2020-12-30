<?php

namespace App\UserActions\IncidentContext;

use Symfony\Component\DependencyInjection\ServiceLocator;

class IncidentContextUserActionsLocator
{

    /**
     * @var ServiceLocator
     */
    private $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(string $class): IncidentContextUserActionInterface
    {
        return $this->locator->get($class);
    }


    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }


    /**
     * @return IncidentContextUserActionInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }
}