<?php

namespace App\ReadModel\ReadModelBuilder\RelationRegistry;

use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Annotation\DTO;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use Doctrine\ORM\Mapping\ClassMetadata;

class RelationRegistry
{

    /**
     * @var RelationsLoadersLocator
     */
    private $relationsLoadersLocator;

    private $loadersToClear = [];


    public function __construct(RelationsLoadersLocator $relationsLoadersLocator)
    {
        $this->relationsLoadersLocator = $relationsLoadersLocator;
    }


    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, array $rows, Metadata $metadata): void
    {
        $dtoPropertiesNames = $metadata->getDTOPropertiesNames($dtoClass);
        $classMetadata      = $metadata->getClassMetadata($entityClass);

        foreach ($dtoPropertiesNames as $fieldName) {
            // пропускаем без аннотации - это не релейшн
            /** @var DTO|null $annotation */
            $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);
            if ( ! $annotation || $annotation->skipLoading) {
                continue;
            }

            $loader = $this->getRelationLoaderForProperty($fieldName, $annotation, $classMetadata);

            $loader->addRelationsToDeferredLoading($entityClass, $dtoClass, $fieldName, $rows, $metadata);

        }

    }


    private function getRelationLoaderForProperty(string $fieldName, DTO $annotation, ClassMetadata $classMetadata): RelationLoaderInterface
    {
        if ($annotation->loader) {
            return $this->getRelationLoader($annotation->loader);
        }

        if ( ! $classMetadata->hasAssociation($fieldName)) {
            throw new \InvalidArgumentException('If DTO property has different from Entity name, you should add relationLoader setting for annotation');
        }

        return $this->getRelationLoader(DefaultRelationLoader::class);
    }


    public function loadRows(string $entityClass, string $dtoClass, Metadata $metadata, User $user): array
    {
        $loaders = $this->getRelationLoadersForEntity($entityClass);

        $allRows = [];

        /** @var RelationLoaderInterface $loader */
        foreach ($loaders as $loader) {
            $rows = $loader->loadRows($entityClass, $dtoClass, $metadata, $user);

            // для большинства будет только один лоадер
            if ( ! count($allRows) && count($rows)) {
                $allRows = $rows;
                continue;
            }

            foreach ($rows as $row) {
                $allRows[] = $row;
            }
        }

        return $allRows;

    }


    private function getRelationLoadersForEntity(string $entityClass): array
    {
        $filtered = [];
        /** @var RelationLoaderInterface $loaderClass */
        foreach ($this->relationsLoadersLocator->getAllClasses() as $loaderClass) {
            if ($loaderClass::supportsEntity($entityClass)) {
                $filtered[] = $this->getRelationLoader($loaderClass);
            }
        }

        return $filtered;
    }


    public function getRowsForProperty(array $row, string $fieldName, string $entityClass, string $dtoClass, Metadata $metadata): ?array
    {
        $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);
        if ( ! $annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }
        $classMetadata = $metadata->getClassMetadata($entityClass);

        $loader = $this->getRelationLoaderForProperty($fieldName, $annotation, $classMetadata);

        return $loader->getRowsForProperty($row, $fieldName, $entityClass, $dtoClass, $metadata);
    }


    private function getRelationLoader(string $loaderClass): RelationLoaderInterface
    {
        if ( ! $this->relationsLoadersLocator->has($loaderClass)) {
            throw new \RuntimeException('Loader ' . $loaderClass . ' not found');
        }

        $loader = $this->relationsLoadersLocator->get($loaderClass);

        if ( ! in_array($loader, $this->loadersToClear, true)) {
            $this->loadersToClear[] = $loader;
        }

        return $loader;
    }


    public function clearState(): void
    {
        /** @var RelationLoaderInterface $loader */
        foreach ($this->loadersToClear as $loader) {
            $loader->clear();
        }

        $this->loadersToClear = [];
    }
}