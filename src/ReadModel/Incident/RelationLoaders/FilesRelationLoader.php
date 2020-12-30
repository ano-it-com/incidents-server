<?php

namespace App\ReadModel\Incident\RelationLoaders;

use App\Entity\File\File;
use App\Entity\File\FileOwnerInterface;
use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationLoaderInterface;
use Doctrine\DBAL\Connection;

class FilesRelationLoader implements RelationLoaderInterface
{
    protected $toLoadEntities = [];

    protected $processed = [];

    protected Connection $connection;

    protected $loadedRows = [];


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public static function supportsEntity(string $entityClass): bool
    {
        return $entityClass === File::class;
    }


    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, $fieldName, array $rows, Metadata $metadata): void
    {
        $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);

        if ( ! $annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }
        $relationDTOClass = $annotation->class;

        /** @var FileOwnerInterface $entityClass */
        $ownerCode = $entityClass::getOwnerCode();

        foreach ($rows as $row) {
            $ownerId = $row['id'];

            $this->toLoadEntities[$relationDTOClass][$ownerCode][] = $ownerId;
        }

    }


    public function loadRows(string $entityClass, string $dtoClass, Metadata $metadata, User $user): array
    {
        if (in_array($dtoClass, $this->processed, true)) {
            return $this->loadedRows[$dtoClass];
        }

        $this->processed[] = $dtoClass;

        if ( ! isset($this->loadedRows[$dtoClass])) {
            $this->loadedRows[$dtoClass] = [];
        }

        $classMetadata = $metadata->getClassMetadata($entityClass);
        $tableName     = $classMetadata->getTableName();

        $qb = $this->connection
            ->createQueryBuilder()
            ->from($tableName)
            ->select('*')
            ->andWhere('deleted != true');

        $parts = [];
        foreach ($this->toLoadEntities as $loaded) {
            foreach ($loaded as $ownerCode => $ids) {
                if ( ! count($ids)) {
                    continue;
                }

                $parts[] = $tableName . '.owner_code = :code_' . $ownerCode . ' AND ' . $tableName . '.owner_id IN (:ids_' . $ownerCode . ')';
                $qb->setParameter('code_' . $ownerCode, $ownerCode);
                $qb->setParameter('ids_' . $ownerCode, $ids, Connection::PARAM_INT_ARRAY);

            }
        }

        if ( ! count($parts)) {
            return [];
        }

        $orExpr = $qb->expr()->or(...$parts);

        $qb->andWhere($orExpr);

        // грузим по ИД
        $stmt = $qb->execute();

        $rows = $stmt->fetchAllAssociative();

        foreach ($rows as $row) {
            $this->loadedRows[$dtoClass][$row['id']] = $row;
        }

        return $this->loadedRows[$dtoClass];
    }


    public function getRowsForProperty(array $row, string $fieldName, string $entityClass, string $dtoClass, Metadata $metadata): ?array
    {
        $targetDtoClass = $metadata->getTargetDtoClassForProperty($fieldName, $entityClass, $dtoClass);

        /** @var FileOwnerInterface $entityClass */
        $ownerCode = $entityClass::getOwnerCode();

        return array_values(array_filter($this->loadedRows[$targetDtoClass], function ($relationRow) use ($row, $ownerCode) {
            return $relationRow['owner_id'] === $row['id'] && $relationRow['owner_code'] === $ownerCode;
        }));
    }


    public function clear(): void
    {
        $this->toLoadEntities = [];
        $this->processed      = [];
        $this->loadedRows     = [];
    }
}