<?php

namespace App\ReadModel\ReadModelBuilder\DTOBuilder;

use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

interface DTOBuilderInterface
{

    public static function supportsDTOClass(string $class): bool;


    public function createDTOFromRow($row, string $entityClass, string $dtoClass, Metadata $metadata, RelationRegistry $relationRegistry): object;
}