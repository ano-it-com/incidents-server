<?php

namespace App\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

trait CommonPropertyHandlerTrait
{
    public static function getSupportedFields(): array
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfo = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor]
        );

        $properties = $propertyInfo->getProperties(static::class);
        $fields = [];

        $getType = function ($types){
            /** @var Type $type */
            foreach ((array)$types as $type){
                if($type = $type->getBuiltinType()){
                    return $type;
                }
            }
            return null;
        };
        foreach ((array)$properties as $property) {
            if ($propertyInfo->isWritable(static::class, $property)) {
                $fields[$property] = [
                    'id' => $property,
                    'name' => $propertyInfo->getShortDescription(static::class, $property),
                    'type' => $getType($propertyInfo->getTypes(static::class, $property)),
                ];
            }
        }

        return $fields;
    }

    public function loadProperties(array $data)
    {
        $supportedFields = array_keys(self::getSupportedFields());
        foreach ($data as $item) {
            $field = $item['id'] ?? null;
            $value = $item['value'] ?? null;
            if (!$field || !$value || !in_array($field, $supportedFields)) {
                continue;
            }
            $this->$field = $value;
        }

        return $this;
    }

    public function getProperties($initPrepared = true): ArrayCollection
    {
        $fields = new ArrayCollection();
        $supportedFields = self::getSupportedFields();
        foreach ($supportedFields as $field => $item) {
            $property = new CommonPropertyHandler($field, $item['type'], $item['name'], $this->$field);
            $preparedMethod = 'getPrepared' . ucfirst($field);
            if ($initPrepared && method_exists(static::class, $preparedMethod)) {
                $property->setPrepared($this->$preparedMethod());
            }
            $fields->set($field, $property);
        }

        return $fields;
    }
}
