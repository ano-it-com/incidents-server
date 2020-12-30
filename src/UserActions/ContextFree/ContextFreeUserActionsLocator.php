<?php

namespace App\UserActions\ContextFree;

use Symfony\Component\DependencyInjection\ServiceLocator;

class ContextFreeUserActionsLocator
{

    /**
     * @var ServiceLocator
     */
    private $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(string $class): ContextFreeUserActionInterface
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