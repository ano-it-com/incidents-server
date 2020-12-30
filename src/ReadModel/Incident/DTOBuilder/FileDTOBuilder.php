<?php

namespace App\ReadModel\Incident\DTOBuilder;

use App\ReadModel\Incident\DTO\Detail\FileDTO;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DefaultDTOBuilder;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DTOBuilderInterface;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

class FileDTOBuilder implements DTOBuilderInterface
{

    private DefaultDTOBuilder $defaultDTOBuilder;


    public function __construct(DefaultDTOBuilder $defaultDTOBuilder)
    {
        $this->defaultDTOBuilder = $defaultDTOBuilder;
    }


    public static function supportsDTOClass(string $class): bool
    {
        return $class === FileDTO::class;
    }


    public function createDTOFromRow($row, string $entityClass, string $dtoClass, Metadata $metadata, RelationRegistry $relationRegistry): object
    {
        /** @var FileDTO $dto */
        $dto = $this->defaultDTOBuilder->createDTOFromRow($row, $entityClass, $dtoClass, $metadata, $relationRegistry);

        // example
        $dto->customField = 1;

        return $dto;
    }
}