<?php

namespace App\ReadModel\ReadModelBuilder\Metadata;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;
use Doctrine\ORM\Mapping\ClassMetadata;

class Metadata
{

    private $classMetadata = [];

    private $dtoProperties = [];

    private $dtoPropertyAnnotations = [];


    public function addEntityClassMetadata(string $entityClass, ClassMetadata $classMetadata): void
    {
        if ( ! isset($this->classMetadata[$entityClass])) {
            $this->classMetadata[$entityClass] = $classMetadata;
        }
    }


    public function addDtoProperty(string $dtoClass, string $propertyName): void
    {
        if ( ! isset($this->dtoProperties[$dtoClass])) {
            $this->dtoProperties[$dtoClass] = [];
        }

        if ( ! in_array($propertyName, $this->dtoProperties[$dtoClass], true)) {
            $this->dtoProperties[$dtoClass][] = $propertyName;
        }
    }


    public function addDtoPropertyAnnotation(string $dtoClass, string $propertyName, DTO $annotation): void
    {
        if ( ! isset($this->dtoPropertyAnnotations[$dtoClass][$propertyName])) {
            $this->dtoPropertyAnnotations[$dtoClass][$propertyName] = $annotation;
        }
    }


    public function isDtoHasProperty(string $dtoClass, string $propertyName): bool
    {
        if ( ! isset($this->dtoProperties[$dtoClass])) {
            throw new \RuntimeException('Metadata not found for DTO ' . $dtoClass);
        }

        return in_array($propertyName, $this->dtoProperties[$dtoClass], true);
    }


    public function getDTOPropertiesNames(string $dtoClass): array
    {
        if ( ! isset($this->dtoProperties[$dtoClass])) {
            throw new \InvalidArgumentException('DTO metadata for ' . $dtoClass . ' not found');
        }

        return $this->dtoProperties[$dtoClass];
    }


    public function getTargetDtoClassForProperty($fieldName, string $entityClass, string $dtoClass): string
    {
        $annotation = $this->getAnnotationForDTOProperty($dtoClass, $fieldName);
        if ( ! $annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }

        return $annotation->class;
    }


    public function getAnnotationForDTOProperty(string $dtoClass, string $propertyName): ?DTO
    {
        return $this->dtoPropertyAnnotations[$dtoClass][$propertyName] ?? null;
    }


    public function getTargetEntityClassForProperty($fieldName, string $entityClass, string $dtoClass): string
    {
        $annotation = $this->getAnnotationForDTOProperty($dtoClass, $fieldName);
        if ($annotation && $annotation->loader) {
            if ( ! $annotation->entity) {
                throw new \RuntimeException('DTO annotation must have Entity class definition, because  ' . $entityClass . ' has no relation with name ' .
                    $fieldName);
            }

            return $annotation->entity;
        }

        $classMetadata = $this->getClassMetadata($entityClass);

        if ( ! $classMetadata->hasAssociation($fieldName)) {
            throw new \InvalidArgumentException('If DTO property has different from Entity name, you should add relationLoader setting for annotation');
        }

        $associationMapping = $classMetadata->getAssociationMapping($fieldName);

        return $associationMapping['targetEntity'];

    }


    public function getClassMetadata(string $entityClass): ClassMetadata
    {
        $classMetadata = $this->classMetadata[$entityClass] ?? null;

        if ( ! $classMetadata) {
            throw new \InvalidArgumentException('ClassMetadata for ' . $entityClass . ' not found');
        }

        return $classMetadata;
    }
}