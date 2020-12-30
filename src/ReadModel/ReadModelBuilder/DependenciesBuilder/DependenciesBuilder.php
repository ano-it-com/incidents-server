<?php

namespace App\ReadModel\ReadModelBuilder\DependenciesBuilder;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;

class DependenciesBuilder
{

    public function build(string $rootEntityClass, string $rootDtoClass, Metadata $metadata): array
    {
        $dependencies = $this->getDependenciesRecursive($rootEntityClass, $rootDtoClass, $metadata);

        // sort by level
        $dependenciesByLevel = [];
        $existOnLevel        = [];
        foreach ($dependencies as $level => $levelDependencies) {
            foreach ($levelDependencies as $method => $entityClass) {
                $currentLevel = $existOnLevel[$entityClass] ?? null;
                if ($currentLevel && $currentLevel < $level) {
                    // переносим на другой уровень
                    foreach ($dependenciesByLevel[$currentLevel] as $oldMethod => $oldEntity) {
                        if ($oldEntity !== $entityClass) {
                            continue;
                        }

                        unset($dependenciesByLevel[$currentLevel][$oldMethod]);
                        $dependenciesByLevel[$level][$oldMethod] = $oldEntity;

                    }
                }
                $existOnLevel[$entityClass]           = $level;
                $dependenciesByLevel[$level][$method] = $entityClass;
            }
        }

        return $dependenciesByLevel;
    }


    private function getDependenciesRecursive(string $rootEntityClass, string $rootDtoClass, Metadata $metadata, int $level = 1, $dependencies = []): array
    {
        $dtoPropertiesNames = $metadata->getDTOPropertiesNames($rootDtoClass);
        $classMetadata      = $metadata->getClassMetadata($rootEntityClass);

        foreach ($dtoPropertiesNames as $fieldName) {
            // пропускаем без аннотации - это не релейшн
            /** @var DTO|null $annotation */
            $annotation = $metadata->getAnnotationForDTOProperty($rootDtoClass, $fieldName);
            if ( ! $annotation || $annotation->skipLoading) {
                continue;
            }

            if (( ! $annotation->loader || ! $annotation->entity) && ! $classMetadata->hasAssociation($fieldName)) {
                throw new \RuntimeException('DTO annotation must have Loader class definitions and Entity class definition, because  ' . $rootEntityClass . ' has no relation with name ' .
                    $fieldName);
            }

            if ($annotation->loader && $annotation->entity) {
                // custom loader
                $targetEntityClass = $annotation->entity;
            } else {
                $associationMapping = $classMetadata->getAssociationMapping($fieldName);
                $targetEntityClass  = $associationMapping['targetEntity'];
            }

            $dtoClass = $annotation->class;

            $code = $rootEntityClass . '::' . $fieldName . '::' . $dtoClass;

            $dependencies[$level][$code] = $targetEntityClass;

            $dependencies = $this->getDependenciesRecursive($targetEntityClass, $dtoClass, $metadata, $level + 1, $dependencies);

        }

        return $dependencies;
    }
}