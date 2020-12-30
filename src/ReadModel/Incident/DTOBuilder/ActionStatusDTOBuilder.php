<?php

namespace App\ReadModel\Incident\DTOBuilder;

use App\Domain\Action\ActionStatusLocator;
use App\ReadModel\Incident\DTO\Detail\ActionStatusDTO;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DefaultDTOBuilder;
use App\ReadModel\ReadModelBuilder\DTOBuilder\DTOBuilderInterface;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;
use App\ReadModel\ReadModelBuilder\RelationRegistry\RelationRegistry;

class ActionStatusDTOBuilder implements DTOBuilderInterface
{
    private DefaultDTOBuilder $defaultDTOBuilder;

    private ActionStatusLocator $actionStatusLocator;

    public function __construct(DefaultDTOBuilder $defaultDTOBuilder, ActionStatusLocator $actionStatusLocator)
    {
        $this->defaultDTOBuilder = $defaultDTOBuilder;
        $this->actionStatusLocator = $actionStatusLocator;
    }


    public static function supportsDTOClass(string $class): bool
    {
        return $class === ActionStatusDTO::class;
    }


    public function createDTOFromRow($row, string $entityClass, string $dtoClass, Metadata $metadata, RelationRegistry $relationRegistry): object
    {
        /** @var ActionStatusDTO $dto */
        $dto = $this->defaultDTOBuilder->createDTOFromRow($row, $entityClass, $dtoClass, $metadata, $relationRegistry);

        if($this->actionStatusLocator->hasByCode($dto->code)){
            $actionStatus = $this->actionStatusLocator->getByCode($dto->code);
            $dto->ttl = $actionStatus::getTtl();
            $dto->title = $actionStatus::getTitle();
        }

        return $dto;
    }
}