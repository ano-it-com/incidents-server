<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use Ramsey\Uuid\Uuid;

class EAVEntity implements EAVPersistableInterface
{

    protected string $id;

    /** @var EAVType */
    protected $type;

    protected int $externalId;

    protected $meta;

    /** @var EAVEntityPropertyValue[] */
    protected array $values = [];


    public function __construct(string $id, EAVType $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getType(): EAVType
    {
        return $this->type;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    /**
     * @return EAVEntityPropertyValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }


    /**
     * @param EAVEntityPropertyValue[] $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }


    public function getPropertyValuesByAlias(string $alias): array
    {
        $typeProperty = $this->type->getPropertyByAlias($alias);

        if ( ! $typeProperty) {
            throw new \InvalidArgumentException('Entity with type ' . $this->type->getAlias() . ' has no property with alias ' . $alias);
        }

        $values = [];
        foreach ($this->values as $value) {
            if ($value->getTypePropertyId() === $typeProperty->getId()) {
                $values[] = $value;
            }
        }

        return $values;
    }


    public function addPropertyValue(EAVEntityPropertyValue $value): void
    {
        $this->values[] = $value;
    }


    public function addPropertyValueByAlias(string $alias, $value): void
    {
        foreach ($this->type->getProperties() as $property) {
            if ($property->getAlias() === $alias) {
                $propertyValue = new EAVEntityPropertyValue(Uuid::uuid4()->toString(), $property);
                $propertyValue->setValue($value);
                $this->values[] = $propertyValue;

                return;
            }
        }

        throw new \InvalidArgumentException('Entity type does not have property with alias ' . $alias);
    }


    public function removeProperty(EAVEntityPropertyValue $propertyValue): void
    {
        foreach ($this->values as $key => $value) {
            if ($value->getId() === $propertyValue->getId()) {
                unset($this->values[$key]);

                return;
            }
        }

        throw new \InvalidArgumentException('Property does not exist');

    }


    public function getExternalId(): int
    {
        return $this->externalId;
    }


    public function setExternalId(int $externalId): void
    {
        $this->externalId = $externalId;
    }

}