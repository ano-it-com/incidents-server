<?php

namespace App\ReadModel\Incident\DTOBuilder;

use App\Domain\Incident\IncidentStatusLocator;
use App\ReadModel\Incident\DTO\Detail\IncidentStatusDTO;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DefaultDTOBuilder;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DTOBuilderInterface;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

class IncidentStatusDTOBuilder implements DTOBuilderInterface
{
    private DefaultDTOBuilder $defaultDTOBuilder;

    private IncidentStatusLocator $incidentStatusLocator;

    public function __construct(DefaultDTOBuilder $defaultDTOBuilder, IncidentStatusLocator $incidentStatusLocator)
    {
        $this->defaultDTOBuilder = $defaultDTOBuilder;
        $this->incidentStatusLocator = $incidentStatusLocator;
    }


    public static function supportsDTOClass(string $class): bool
    {
        return $class === IncidentStatusDTO::class;
    }


    public function createDTOFromRow($row, string $entityClass, string $dtoClass, Metadata $metadata, RelationRegistry $relationRegistry): object
    {
        /** @var IncidentStatusDTO $dto */
        $dto = $this->defaultDTOBuilder->createDTOFromRow($row, $entityClass, $dtoClass, $metadata, $relationRegistry);

        if($this->incidentStatusLocator->hasByCode($dto->code)){
            $incidentStatusClass = $this->incidentStatusLocator->getByCode($dto->code);
            $dto->ttl = $incidentStatusClass::getTtl();
            $dto->title = $incidentStatusClass::getTitle();
        }

        return $dto;
    }
}