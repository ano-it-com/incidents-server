<?php

namespace App\ReadModel\ReadModelBuilder\Metadata;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionProperty;

class MetadataCollector implements MetadataCollectorInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Reader
     */
    private $annotationReader;


    public function __construct(EntityManagerInterface $em, Reader $annotationReader)
    {
        $this->em               = $em;
        $this->annotationReader = $annotationReader;
    }


    public function collect($rootEntityClass, $rootDtoClass): Metadata
    {
        $metadata = new Metadata();

        $this->processEntityRecursive($rootEntityClass, $metadata);
        $this->processDtoRecursive($rootDtoClass, $metadata);

        return $metadata;
    }


    private function processEntityRecursive(string $rootEntityClass, Metadata $metadata, array $processed = []): void
    {
        $classMetadata = $this->em->getClassMetadata($rootEntityClass);

        $metadata->addEntityClassMetadata($rootEntityClass, $classMetadata);

        $processed[] = $rootEntityClass;

        foreach ($classMetadata->associationMappings as $fieldName => $associationMapping) {
            $targetEntityClass = $associationMapping['targetEntity'];

            if (in_array($targetEntityClass, $processed, true)) {
                continue;
            }

            $targetClassMetadata = $this->em->getClassMetadata($targetEntityClass);

            $metadata->addEntityClassMetadata($targetEntityClass, $targetClassMetadata);

            $processed[] = $targetEntityClass;

            $this->processEntityRecursive($targetEntityClass, $metadata, $processed);
        }

    }


    private function processDtoRecursive(string $rootDtoClass, Metadata $metadata, array $processed = []): void
    {
        if (in_array($rootDtoClass, $processed, true)) {
            return;
        }

        $reflectionClass = new ReflectionClass($rootDtoClass);

        $processed[] = $rootDtoClass;

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $metadata->addDtoProperty($rootDtoClass, $reflectionProperty->getName());

            /** @var DTO|null $annotation */
            $annotation = $this->annotationReader->getPropertyAnnotation($reflectionProperty, DTO::class);

            if ( ! $annotation) {
                continue;
            }

            if ($annotation->entity) {
                $metadata->addEntityClassMetadata($annotation->entity, $this->em->getClassMetadata($annotation->entity));
            }

            $metadata->addDtoPropertyAnnotation($rootDtoClass, $reflectionProperty->getName(), $annotation);

            $childDTOClass = $annotation->class;

            $this->processDtoRecursive($childDTOClass, $metadata, $processed);
        }
    }
}