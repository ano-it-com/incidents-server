<?php

namespace App\ReadModel\ReadModelBuilder\DTOBuilder;

use App\ReadModel\ReadModelBuilder\Annotation\DTO;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;
use App\ReadModel\ReadModelBuilder\ValueConverter\ValueConverter;

class DefaultDTOBuilder implements DTOBuilderInterface
{

    /**
     * @var ValueConverter
     */
    private $valueConverter;

    private $createdDtos = [];

    /**
     * @var DTOBuilderLocator
     */
    private $DTOBuilderLocator;


    public function __construct(ValueConverter $valueConverter, DTOBuilderLocator $DTOBuilderLocator)
    {
        $this->valueConverter    = $valueConverter;
        $this->DTOBuilderLocator = $DTOBuilderLocator;
    }


    public static function supportsDTOClass(string $class): bool
    {
        return true;
    }


    public function createDTOFromRow($row, string $entityClass, string $dtoClass, Metadata $metadata, RelationRegistry $relationRegistry): object
    {
        if (isset($this->createdDtos[$dtoClass][$row['id']])) {
            return $this->createdDtos[$dtoClass][$row['id']];
        }

        $dto = new $dtoClass();

        $dtoPropertiesNames = $metadata->getDTOPropertiesNames($dtoClass);
        $classMetadata      = $metadata->getClassMetadata($entityClass);

        foreach ($dtoPropertiesNames as $fieldName) {
            // пропускаем с аннотацией - это релейшн
            /** @var DTO|null $annotation */
            $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);
            if ($annotation) {
                continue;
            }

            if ( ! $classMetadata->hasField($fieldName)) {
                continue;
            }

            $fieldMapping = $classMetadata->getFieldMapping($fieldName);

            $columnName = $fieldMapping['columnName'];

            $dto->{$fieldName} = $this->valueConverter->convertToPHPValue($row[$columnName], $fieldMapping['type']);

        }

        foreach ($dtoPropertiesNames as $fieldName) {
            // пропускаем без аннотации - это не релейшн
            /** @var DTO|null $annotation */
            $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);
            if ( ! $annotation) {
                continue;
            }

            $rows = $relationRegistry->getRowsForProperty($row, $fieldName, $entityClass, $dtoClass, $metadata);

            $childDtoClass    = $metadata->getTargetDtoClassForProperty($fieldName, $entityClass, $dtoClass);
            $childEntityClass = $metadata->getTargetEntityClassForProperty($fieldName, $entityClass, $dtoClass);

            if ($rows === null) {
                $dto->{$fieldName} = null;
                continue;
            }

            if ( ! count($rows)) {
                $dto->{$fieldName} = [];
                continue;
            }

            $isMultiple = $this->isMultiple($rows);

            $builder = $this->DTOBuilderLocator->getBuilderForDTOClass($childDtoClass);

            if ($isMultiple) {
                $dto->{$fieldName} = array_map(function ($row) use ($childEntityClass, $childDtoClass, $metadata, $relationRegistry, $builder) {
                    return $builder->createDTOFromRow($row, $childEntityClass, $childDtoClass, $metadata, $relationRegistry);
                }, array_values($rows));
            } else {
                $dto->{$fieldName} = $builder->createDTOFromRow($rows, $childEntityClass, $childDtoClass, $metadata, $relationRegistry);
            }
        }

        $this->createdDtos[$dtoClass][$dto->id] = $dto;

        return $dto;
    }


    private function isMultiple(array $rows): bool
    {
        if (isset($rows['id'])) {
            return false;
        }

        return true;
    }

}