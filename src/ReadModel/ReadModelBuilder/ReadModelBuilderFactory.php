<?php

namespace App\ReadModel\ReadModelBuilder;

use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\DependenciesBuilder\DependenciesBuilder;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DTOBuilderLocator;
use App\ReadModel\ReadModelBuilder\Metadata\CachedMetadataCollector;
use App\ReadModel\ReadModelBuilder\Metadata\MetadataCollector;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

class ReadModelBuilderFactory
{

    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /**
     * @var DependenciesBuilder
     */
    private $dependenciesBuilder;

    /**
     * @var RelationRegistry
     */
    private $relationRegistry;

    /**
     * @var DTOBuilderLocator
     */
    private $DTOBuilderLocator;


    public function __construct(
        CachedMetadataCollector $metadataCollector,
        DependenciesBuilder $dependenciesBuilder,
        RelationRegistry $relationRegistry,
        DTOBuilderLocator $DTOBuilderLocator
    ) {
        $this->metadataCollector   = $metadataCollector;
        $this->dependenciesBuilder = $dependenciesBuilder;
        $this->relationRegistry    = $relationRegistry;
        $this->DTOBuilderLocator   = $DTOBuilderLocator;
    }


    public function make(User $user, array $rows, string $rootEntityClass, string $rootDtoClass): ReadModelBuilder
    {
        return new ReadModelBuilder($this->metadataCollector, $this->dependenciesBuilder, $this->relationRegistry, $this->DTOBuilderLocator,$user,  $rows, $rootEntityClass, $rootDtoClass);
    }
}