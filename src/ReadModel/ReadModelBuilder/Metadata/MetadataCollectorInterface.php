<?php

namespace App\ReadModel\ReadModelBuilder\Metadata;

interface MetadataCollectorInterface
{

    public function collect($rootEntityClass, $rootDtoClass): Metadata;
}