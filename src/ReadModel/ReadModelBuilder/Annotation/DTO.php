<?php

namespace App\ReadModel\ReadModelBuilder\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class DTO
{

    public $class;

    public $entity;

    public $loader;

    public $skipLoading = false;
}