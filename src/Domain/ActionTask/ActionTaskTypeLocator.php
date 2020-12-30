<?php

namespace App\Domain\ActionTask;

use Symfony\Component\DependencyInjection\ServiceLocator;

class ActionTaskTypeLocator
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getByCode(string $code): ActionTaskTypeInterface
    {
        return $this->locator->get($code);
    }

    public function getIdToTitleList(): array
    {
        $classes = $this->getAllClasses();

        $idToTitleList = [];

        /** @var ActionTaskTypeInterface $class */
        foreach ($classes as $class) {
            $idToTitleList[$class::getCode()] = $class::getTitle();
        }

        sort($idToTitleList);

        return $idToTitleList;
    }

    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }

    public function getClassByCode(string $code): string
    {
        if ( ! $this->hasByCode($code)) {
            throw new \InvalidArgumentException('Action Task status not found for code ' . $code);
        }

        $classes = $this->locator->getProvidedServices();

        return $classes[$code];
    }

    public function hasByCode(string $code): bool
    {
        return $this->locator->has($code);
    }
}
