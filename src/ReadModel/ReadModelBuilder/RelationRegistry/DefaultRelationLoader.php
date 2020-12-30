<?php

namespace App\ReadModel\ReadModelBuilder\RelationRegistry;

use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DefaultRelationLoader implements RelationLoaderInterface
{

    private $loadEntitiesById = [];

    private $loadEntitiesForField = [];

    private $loadEntitiesWithPivot = [];

    private $loadedRowsByPivotMapping = [];

    private $loadedRows = [];

    private $processed = [];

    /**
     * @var Connection
     */
    private $connection;


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public static function supportsEntity(string $entityClass): bool
    {
        return true;
    }


    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, $fieldName, array $rows, Metadata $metadata): void
    {
        $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);

        if ( ! $annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }
        $relationDTOClass = $annotation->class;

        $classMetadata = $metadata->getClassMetadata($entityClass);
        if ( ! $classMetadata->hasAssociation($fieldName)) {
            throw new \InvalidArgumentException('Entity relation ' . $fieldName . '  not found for entity  ' . $entityClass);
        }

        $associationMapping = $classMetadata->getAssociationMapping($fieldName);

        $targetEntityClass   = $associationMapping['targetEntity'];
        $targetClassMetadata = $metadata->getClassMetadata($targetEntityClass);

        switch ($associationMapping['type']) {
            case ClassMetadataInfo::ONE_TO_ONE:
            case ClassMetadataInfo::MANY_TO_ONE:
                $this->addToDeferredLoadingForLocalKey($relationDTOClass, $associationMapping, $rows);

                break;
            case ClassMetadataInfo::ONE_TO_MANY:
                $this->addToDeferredLoadingForForeignKey($relationDTOClass, $associationMapping, $rows, $targetClassMetadata);

                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                $this->addToDeferredLoadingForM2M($relationDTOClass, $associationMapping, $rows);

                break;
            default:
                throw new \RuntimeException('Relation type ' . $associationMapping['type'] . ' can\'t be handled');
        }
    }


    private function addToDeferredLoadingForLocalKey(string $relationDTOClass, array $associationMapping, array $rows): void
    {
        $fields                = array_keys($associationMapping['sourceToTargetKeyColumns']);
        $sourceEntityFieldName = reset($fields);

        foreach ($rows as $row) {
            $value = $row[$sourceEntityFieldName];

            if ( ! isset($this->loadEntitiesById[$relationDTOClass])) {
                $this->loadEntitiesById[$relationDTOClass] = [];
            }

            if ($value === null) {
                continue;
            }

            if ( ! in_array($value, $this->loadEntitiesById[$relationDTOClass], true)) {
                $this->loadEntitiesById[$relationDTOClass][] = $value;
            }
        }

    }


    private function addToDeferredLoadingForForeignKey($relationDTOClass, array $associationMapping, array $rows, ClassMetadata $targetClassMetadata): void
    {
        $targetAssociationFieldMapping = $targetClassMetadata->associationMappings[$associationMapping['mappedBy']];

        $fields                = array_keys($targetAssociationFieldMapping['sourceToTargetKeyColumns']);
        $sourceEntityFieldName = reset($fields);
        $targetEntityFiledName = reset($targetAssociationFieldMapping['sourceToTargetKeyColumns']);

        foreach ($rows as $row) {
            $value = $row[$targetEntityFiledName];

            if ( ! isset($this->loadEntitiesForField[$relationDTOClass][$sourceEntityFieldName])) {
                $this->loadEntitiesForField[$relationDTOClass][$sourceEntityFieldName] = [];
            }

            if ( ! in_array($value, $this->loadEntitiesForField[$relationDTOClass][$sourceEntityFieldName], true)) {
                $this->loadEntitiesForField[$relationDTOClass][$sourceEntityFieldName][] = $value;
            }
        }

    }


    private function addToDeferredLoadingForM2M($relationDTOClass, array $associationMapping, array $rows): void
    {
        $pivotTable = $associationMapping['joinTable']['name'];

        $fields           = array_keys($associationMapping['relationToSourceKeyColumns']);
        $sourceColumnName = reset($fields);

        $fields           = array_keys($associationMapping['relationToTargetKeyColumns']);
        $targetColumnName = reset($fields);

        $sourceFieldName = reset($associationMapping['relationToSourceKeyColumns']);

        $code = $sourceColumnName . '::' . $targetColumnName;

        foreach ($rows as $row) {
            $value = $row[$sourceFieldName];

            if ( ! isset($this->loadEntitiesWithPivot[$relationDTOClass][$pivotTable][$code])) {
                $this->loadEntitiesWithPivot[$relationDTOClass][$pivotTable][$code] = [];
            }

            if ( ! in_array($value, $this->loadEntitiesWithPivot[$relationDTOClass][$pivotTable][$code], true)) {
                $this->loadEntitiesWithPivot[$relationDTOClass][$pivotTable][$code][] = $value;
            }
        }
    }


    // TODO разбить на три типа лоадеров и вызывать их отсюда. И наследоваться проще
    public function loadRows(string $entityClass, string $dtoClass, Metadata $metadata, User $user): array
    {
        if (in_array($dtoClass, $this->processed, true)) {
            return $this->loadedRows[$dtoClass];
        }

        $this->processed[] = $dtoClass;

        $classMetadata = $metadata->getClassMetadata($entityClass);
        $tableName     = $classMetadata->getTableName();

        if ( ! isset($this->loadedRows[$dtoClass])) {
            $this->loadedRows[$dtoClass] = [];
        }

        if ( ! isset($this->loadEntitiesById[$dtoClass]) && ! isset($this->loadEntitiesForField[$dtoClass]) && ! isset($this->loadEntitiesWithPivot[$dtoClass])) {
            return [];
        }

        // handle deleted and active
        $hasActiveField  = $classMetadata->hasField('active');
        $hasDeletedField = $classMetadata->hasField('deleted');

        // собираем все способы загрузки
        if (isset($this->loadEntitiesById[$dtoClass])) {
            // грузим по ИД
            $qb = $this->connection
                ->createQueryBuilder()
                ->from($tableName)
                ->select('*')
                ->andWhere($tableName . '.id IN (:ids)')
                ->setParameter('ids', $this->loadEntitiesById[$dtoClass], Connection::PARAM_INT_ARRAY);

            $this->applyActiveOnly($qb, $tableName, $hasActiveField);
            $this->applyNotDeletedOnly($qb, $tableName, $hasDeletedField);

            $stmt = $qb->execute();

            $rows = $stmt->fetchAllAssociative();

            foreach ($rows as $row) {
                $this->loadedRows[$dtoClass][$row['id']] = $row;
            }
        }

        if (isset($this->loadEntitiesForField[$dtoClass])) {

            foreach ($this->loadEntitiesForField[$dtoClass] as $fieldName => $ids) {
                // грузим по ИД
                $qb = $this->connection
                    ->createQueryBuilder()
                    ->from($tableName)
                    ->select('*')
                    ->andWhere($tableName . '.' . $fieldName . ' IN (:ids)')
                    ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

                $this->applyActiveOnly($qb, $tableName, $hasActiveField);
                $this->applyNotDeletedOnly($qb, $tableName, $hasDeletedField);

                $stmt = $qb->execute();

                $rows = $stmt->fetchAllAssociative();

                foreach ($rows as $row) {
                    $this->loadedRows[$dtoClass][$row['id']] = $row;
                }
            }
        }

        if (isset($this->loadEntitiesWithPivot[$dtoClass])) {
            // грузим по соединенной
            foreach ($this->loadEntitiesWithPivot[$dtoClass] as $pivotTable => $info) {
                foreach ($info as $code => $ids) {
                    [ $sourceColumn, $targetColumn ] = explode('::', $code);

                    // грузим по ИД
                    $qb = $this->connection
                        ->createQueryBuilder()
                        ->from($tableName)
                        ->select($tableName . '.*, ' . $pivotTable . '.' . $sourceColumn)
                        ->leftJoin($tableName, $pivotTable, $pivotTable, $tableName . '.id = ' . $pivotTable . '.' . $targetColumn)
                        ->andWhere($pivotTable . '.' . $sourceColumn . ' IN (:ids)')
                        ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

                    $this->applyActiveOnly($qb, $tableName, $hasActiveField);
                    $this->applyNotDeletedOnly($qb, $tableName, $hasDeletedField);

                    $stmt = $qb->execute();

                    $rows = $stmt->fetchAllAssociative();

                    if ( ! isset($this->loadedRows[$dtoClass])) {
                        $this->loadedRows[$dtoClass] = [];
                    }

                    foreach ($rows as $row) {
                        $this->loadedRowsByPivotMapping[$pivotTable][] = [
                            $sourceColumn => $row[$sourceColumn],
                            $targetColumn => $row['id'],
                        ];
                        unset($row[$sourceColumn]);
                        $this->loadedRows[$dtoClass][$row['id']] = $row;
                    }
                }
            }
        }

        return $this->loadedRows[$dtoClass];
    }


    public function getRowsForProperty(array $row, string $fieldName, string $entityClass, string $dtoClass, Metadata $metadata): ?array
    {
        $classMetadata      = $metadata->getClassMetadata($entityClass);
        $associationMapping = $classMetadata->getAssociationMapping($fieldName);
        $childDtoClass      = $metadata->getTargetDtoClassForProperty($fieldName, $entityClass, $dtoClass);
        $targetEntityClass  = $metadata->getTargetEntityClassForProperty($fieldName, $entityClass, $dtoClass);

        switch ($associationMapping['type']) {
            case ClassMetadataInfo::ONE_TO_ONE:
            case ClassMetadataInfo::MANY_TO_ONE:
                $fields                = array_keys($associationMapping['sourceToTargetKeyColumns']);
                $sourceEntityFieldName = reset($fields);

                $entityId = $row[$sourceEntityFieldName];

                if ($entityId === null) {
                    return null;
                }

                $childRow = $this->loadedRows[$childDtoClass][$entityId] ?? null;

                if ( ! $childRow) {
                    throw new \RuntimeException('Entity for DTO class ' . $childDtoClass . ' with id ' . $entityId . ' not found');
                }

                return $childRow;
            case ClassMetadataInfo::ONE_TO_MANY:
                $targetClassMetadata           = $metadata->getClassMetadata($targetEntityClass);
                $targetAssociationFieldMapping = $targetClassMetadata->associationMappings[$associationMapping['mappedBy']];

                $fields                = array_keys($targetAssociationFieldMapping['sourceToTargetKeyColumns']);
                $sourceEntityFieldName = reset($fields);
                $targetEntityFiledName = reset($targetAssociationFieldMapping['sourceToTargetKeyColumns']);

                $parentEntityId = $row[$targetEntityFiledName];

                $allRows = $this->loadedRows[$childDtoClass];

                if ( ! count($allRows)) {
                    return [];
                }

                return array_filter($allRows, function ($row) use ($sourceEntityFieldName, $parentEntityId) {
                    return $row[$sourceEntityFieldName] === $parentEntityId;
                });
            case ClassMetadataInfo::MANY_TO_MANY:
                $pivotTable = $associationMapping['joinTable']['name'];

                $fields           = array_keys($associationMapping['relationToSourceKeyColumns']);
                $sourceColumnName = reset($fields);

                $fields           = array_keys($associationMapping['relationToTargetKeyColumns']);
                $targetColumnName = reset($fields);

                if ( ! isset($this->loadedRowsByPivotMapping[$pivotTable])) {
                    return [];
                }

                $pivotMapping = $this->loadedRowsByPivotMapping[$pivotTable];

                $childIds = array_map(function ($row) use ($targetColumnName) {
                    return $row[$targetColumnName];
                }, array_filter($pivotMapping, function ($pivotRow) use ($sourceColumnName, $row) {
                    return $pivotRow[$sourceColumnName] === $row['id'];
                }));

                $allRows = $this->loadedRows[$childDtoClass];

                if ( ! count($allRows)) {
                    // нет связей
                    break;
                }

                $childRows = array_filter($allRows, function ($row) use ($childIds) {
                    return in_array($row['id'], $childIds, true);
                });

                if (count($childIds) !== count($childRows)) {
                    throw new \RuntimeException('Can\'t find all rows for entity ' . $childDtoClass);
                }

                return $childRows;
            default:
                throw new \RuntimeException('Relation type ' . $associationMapping['type'] . ' can\'t be handled');
        }

        return null;
    }


    private function applyActiveOnly(QueryBuilder $qb, string $tableName, bool $hasActiveField): void
    {
        if ($hasActiveField) {
            $qb->andWhere($tableName . '.active = true');
        }


    }


    private function applyNotDeletedOnly(QueryBuilder $qb, string $tableName, bool $hasDeletedField): void
    {
        if ($hasDeletedField) {
            $qb->andWhere($tableName . '.deleted != true');
        }
    }


    public function clear(): void
    {
        $this->loadEntitiesById         = [];
        $this->loadEntitiesForField     = [];
        $this->loadEntitiesWithPivot    = [];
        $this->loadedRowsByPivotMapping = [];
        $this->loadedRows               = [];
        $this->processed                = [];
    }
}