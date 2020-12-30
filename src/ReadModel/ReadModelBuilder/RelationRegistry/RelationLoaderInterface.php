<?php

namespace App\ReadModel\ReadModelBuilder\RelationRegistry;

use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;

interface RelationLoaderInterface
{

    public static function supportsEntity(string $entityClass): bool;


    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, $fieldName, array $rows, Metadata $metadata): void;


    public function loadRows(string $entityClass, string $dtoClass, Metadata $metadata, User $user): array;


    public function getRowsForProperty(array $row, string $fieldName, string $entityClass, string $dtoClass, Metadata $metadata): ?array;


    public function clear(): void;
}