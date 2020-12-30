<?php

namespace App\ReadModel\Incident\RelationLoaders;

use App\Entity\Incident\Action\Action;
use App\Entity\Security\User;
use App\ReadModel\Loaders\Incident\Criteria\UserPermissionsCriteria;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationLoaderInterface;
use Doctrine\DBAL\Connection;

class ActionsRelationLoader implements RelationLoaderInterface
{

    /**
     * @var Connection
     */
    private $connection;

    private $loadEntitiesForField = [];

    private $processed = [];

    private $loadedRows = [];

    /**
     * @var UserPermissionsCriteria
     */
    private $userPermissionsCriteria;


    public function __construct(Connection $connection, UserPermissionsCriteria $userPermissionsCriteria)
    {
        $this->connection              = $connection;
        $this->userPermissionsCriteria = $userPermissionsCriteria;
    }


    public static function supportsEntity(string $entityClass): bool
    {
        return $entityClass === Action::class;
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

        if ( ! isset($this->loadEntitiesForField[$dtoClass])) {
            return [];
        }

        foreach ($this->loadEntitiesForField[$dtoClass] as $fieldName => $ids) {
            // грузим по ИД
            $qb = $this->connection
                ->createQueryBuilder()
                ->from($tableName)
                ->select($tableName . '.*')
                ->andWhere($tableName . '.' . $fieldName . ' IN (:ids)')
                ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

            $this->userPermissionsCriteria->applyToActionsQuery($qb, $user);

            $stmt = $qb->execute();

            $rows = $stmt->fetchAllAssociative();

            foreach ($rows as $row) {
                $this->loadedRows[$dtoClass][$row['id']] = $row;
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

    }


    public function clear(): void
    {
        $this->loadEntitiesForField = [];
        $this->processed            = [];
        $this->loadedRows           = [];
    }
}