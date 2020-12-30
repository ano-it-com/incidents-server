<?php

namespace App\ReadModel\ReadModelBuilder;

use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\DependenciesBuilder\DependenciesBuilder;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DTOBuilderLocator;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\Metadata\MetadataCollector;
use App\ReadModel\ReadModelBuilder\Metadata\MetadataCollectorInterface;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

class ReadModelBuilder
{

    private $loadedRows = [];

    private $rootRows;

    private $rootEntityClass;

    private $rootDtoClass;

    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /** @var Metadata */
    private $metadata;

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

    /**
     * @var User
     */
    private $user;


    public function __construct(
        MetadataCollectorInterface $metadataCollector,
        DependenciesBuilder $dependenciesBuilder,
        RelationRegistry $relationRegistry,
        DTOBuilderLocator $DTOBuilderLocator,
        User $user,
        array $rows,
        string $rootEntityClass,
        string $rootDtoClass
    ) {
        $this->metadataCollector   = $metadataCollector;
        $this->dependenciesBuilder = $dependenciesBuilder;
        $this->user                = $user;
        $this->rootRows            = $rows;
        $this->rootEntityClass     = $rootEntityClass;
        $this->rootDtoClass        = $rootDtoClass;
        $this->relationRegistry    = $relationRegistry;
        $this->DTOBuilderLocator   = $DTOBuilderLocator;

        foreach ($rows as $row) {
            $this->loadedRows[$rootEntityClass][$row['id']] = $row;
        }
    }


    public function build(): array
    {
        // metadata collector
        $this->metadata = $this->metadataCollector->collect($this->rootEntityClass, $this->rootDtoClass);

        // сторим зависимости по уровням, чтобы понять в какой последовательности грузить сущности
        $dependencies = $this->dependenciesBuilder->build($this->rootEntityClass, $this->rootDtoClass, $this->metadata);

        // загружаем все сущности по порядку зависимостей
        $this->loadAllRelations($dependencies);

        // сторим ДТО
        $dtos = $this->buildDTOs();

        $this->relationRegistry->clearState();

        return $dtos;
    }


    private function loadAllRelations(array $dependencies): void
    {
        $this->relationRegistry->addRelationsToDeferredLoading($this->rootEntityClass, $this->rootDtoClass, $this->rootRows, $this->metadata);

        foreach ($dependencies as $level => $levelDependencies) {
            foreach ($levelDependencies as $code => $childEntityClass) {
                [ $rootEntityClass, $fieldName, $childDtoClass ] = explode('::', $code);

                $loadedRows = $this->relationRegistry->loadRows($childEntityClass, $childDtoClass, $this->metadata, $this->user);

                $this->relationRegistry->addRelationsToDeferredLoading($childEntityClass, $childDtoClass, $loadedRows, $this->metadata);
            }
        }
    }


    private function buildDTOs(): array
    {
        $builder = $this->DTOBuilderLocator->getBuilderForDTOClass($this->rootDtoClass);

        $DTOs = [];
        foreach ($this->rootRows as $rootRow) {
            $dto    = $builder->createDTOFromRow($rootRow, $this->rootEntityClass, $this->rootDtoClass, $this->metadata, $this->relationRegistry);
            $DTOs[] = $dto;
        }

        return $DTOs;
    }

}