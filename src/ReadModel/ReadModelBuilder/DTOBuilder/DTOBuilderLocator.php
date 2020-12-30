<?php

namespace App\ReadModel\ReadModelBuilder\DTOBuilder;

use Symfony\Component\DependencyInjection\ServiceLocator;

class DTOBuilderLocator
{

    /**
     * @var ServiceLocator
     */
    private $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function has(string $class): bool
    {
        return $this->locator->has($class);
    }


    public function getBuilderForDTOClass(string $dtoClass): DTOBuilderInterface
    {
        /** @var DTOBuilderInterface $class */
        foreach ($this->getAllClasses() as $class) {
            if ($class === DefaultDTOBuilder::class) {
                continue;
            }

            if ($class::supportsDTOClass($dtoClass)) {
                return $this->get($class);
            }
        }

        return $this->get(DefaultDTOBuilder::class);
    }


    public function getAllClasses(): array
    {
        return $this->locator->getProvidedServices();
    }


    public function get(string $class): DTOBuilderInterface
    {
        return $this->locator->get($class);
    }
}