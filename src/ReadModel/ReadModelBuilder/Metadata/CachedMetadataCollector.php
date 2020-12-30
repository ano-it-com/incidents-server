<?php

namespace App\ReadModel\ReadModelBuilder\Metadata;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedMetadataCollector implements MetadataCollectorInterface
{

    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /**
     * @var CacheInterface
     */
    private $cache;


    public function __construct(MetadataCollector $metadataCollector, CacheInterface $cache)
    {
        $this->metadataCollector = $metadataCollector;
        $this->cache             = $cache;
    }


    public function collect($rootEntityClass, $rootDtoClass): Metadata
    {
        $key = str_replace('\\', '__', 'readModelMetadata_' . $rootEntityClass . '_' . $rootDtoClass);

        return $this->cache->get($key, function (ItemInterface $item) use ($rootEntityClass, $rootDtoClass) {
            $item->expiresAfter(60);

            return $this->metadataCollector->collect($rootEntityClass, $rootDtoClass);
        });
    }
}