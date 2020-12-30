<?php

namespace App\ReadModel\Incident\RelationLoaders;

use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Comment\Comment;
use App\Entity\Incident\Incident;
use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationLoaderInterface;
use Doctrine\DBAL\Connection;

class CommentsRelationLoader implements RelationLoaderInterface
{

    private $toLoadEntities = [];

    private $processed = [];

    /**
     * @var Connection
     */
    private $connection;

    private $loadedRows = [];


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public static function supportsEntity(string $entityClass): bool
    {
        return $entityClass === Comment::class;
    }


    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, $fieldName, array $rows, Metadata $metadata): void
    {
        // грузим только для инцидентов, потому что для действий будут они же
        if ($entityClass !== Incident::class) {
            return;
        }

        $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);

        if ( ! $annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }
        $relationDTOClass = $annotation->class;

        if ( ! isset($this->toLoadEntities[$relationDTOClass])) {
            $this->toLoadEntities[$relationDTOClass] = [];
        }

        foreach ($rows as $row) {
            $id = $row['id'];

            $this->toLoadEntities[$relationDTOClass][] = $id;
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

        $ids = $this->toLoadEntities[$dtoClass];
        $qb->andWhere($tableName . '.incident_id IN (:ids)');
        $qb->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

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

        if ($entityClass === Incident::class) {
            return array_values(array_filter($this->loadedRows[$targetDtoClass], function ($relationRow) use ($row) {
                return $relationRow['action_id'] === null && $relationRow['incident_id'] === $row['id'];
            }));
        }
        if ($entityClass === Action::class) {
            return array_values(array_filter($this->loadedRows[$targetDtoClass], function ($relationRow) use ($row) {
                return $relationRow['action_id'] === $row['id'];
            }));
        }

        return [];

    }


    public function clear(): void
    {
        $this->toLoadEntities = [];
        $this->processed      = [];
        $this->loadedRows     = [];
    }
}