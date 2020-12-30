<?php

namespace App\Domain\Action;

use Symfony\Component\DependencyInjection\ServiceLocator;

class ActionStatusLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getByCode(string $code): ActionStatusInterface
    {
        return $this->locator->get($code);
    }

    public function getIdToTitleList(): array
    {
        $classes = $this->getAllClasses();

        $idToTitleList = [];

        foreach ($classes as $class) {
            $idToTitleList[$class::getCode()] = $class::getTitle();
        }

        return $idToTitleList;
    }

    /**
     * @return ActionStatusInterface[]
     */
    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }

    public function getClassByCode(string $code): string
    {
        if ( ! $this->hasByCode($code)) {
            throw new \InvalidArgumentException('Action status not found for code ' . $code);
        }

        $classes = $this->locator->getProvidedServices();

        return $classes[$code];

    }

    public function hasByCode(string $code): bool
    {
        return $this->locator->has($code);
    }
}
