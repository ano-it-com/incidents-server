<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL;

class ValueTypes
{

    /** @var ValueTypeInterface[] */
    private array $typesByCode;

    /** @var ValueTypeInterface[] */
    private array $typesByClass;


    public function __construct(iterable $types)
    {
        /** @var ValueTypeInterface $type */
        foreach ($types as $type) {
            $code = $type->getCode();
            if (isset($this->typesByCode[$code])) {
                throw new \RuntimeException('Each Value Type must have unique code. Duplicate code for ' . $code);
            }
            $this->typesByCode[$code]             = $type;
            $this->typesByClass[get_class($type)] = $type;
        }
    }


    public function getByCode(int $valueTypeCode): ValueTypeInterface
    {
        $type = $this->typesByCode[$valueTypeCode] ?? null;

        if ( ! $type) {
            throw new \RuntimeException('Type with code ' . $valueTypeCode . ' not found!');
        }

        return $type;
    }


    public function getByClass(string $className): ValueTypeInterface
    {
        $type = $this->typesByClass[$className] ?? null;

        if ( ! $type) {
            throw new \RuntimeException('Type with class ' . $className . ' not found!');
        }

        return $type;
    }

}