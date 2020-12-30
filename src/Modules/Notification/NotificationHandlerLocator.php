<?php

namespace App\Modules\Notification;

use Symfony\Component\DependencyInjection\ServiceLocator;

class NotificationHandlerLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function get(string $class): NotificationHandlerInterface
    {
        return $this->locator->get($class);
    }

    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }

    /**
     * @return NotificationHandlerInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }
}
