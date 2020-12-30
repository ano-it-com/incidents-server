<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVType implements EAVPersistableInterface
{

    protected string $id;

    protected string $alias;

    protected string $title;

    protected $meta;

    /** @var EAVTypeProperty[] */
    protected array $properties = [];


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getAlias(): string
    {
        return $this->alias;
    }


    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * @return EAVTypeProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }


    /**
     * @param EAVTypeProperty[] $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }


    public function getPropertyById(string $id): ?EAVTypeProperty
    {
        foreach ($this->properties as $property) {
            if ($property->getId() === $id) {
                return $property;
            }
        }

        return null;
    }


    public function getPropertyByAlias(string $alias): ?EAVTypeProperty
    {
        foreach ($this->properties as $property) {
            if ($property->getAlias() === $alias) {
                return $property;
            }
        }

        return null;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }

}