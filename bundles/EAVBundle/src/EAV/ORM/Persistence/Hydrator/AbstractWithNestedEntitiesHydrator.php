<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

abstract class AbstractWithNestedEntitiesHydrator
{

    protected EAVEntityManagerInterface $em;

    private NamesConverter $namesConverter;


    public function __construct(EAVEntityManagerInterface $em, NamesConverter $namesConverter)
    {
        $this->em             = $em;
        $this->namesConverter = $namesConverter;
    }


    public function hydrate(array $entityRows): array
    {
        $uow         = $this->em->getUnitOfWork();
        $entityClass = $this->getEntityClass();

        $entities = [];

        foreach ($entityRows as $entityData) {
            $id = $entityData['id'];

            if ($uow->has($entityClass, $id)) {
                $entity = $uow->get($entityClass, $id);
            } else {
                $entity = $this->buildEntity($entityData);
                $uow->registerManaged($entity, $this->removeTemporaryKeys($entityData));
            }

            $entities[] = $entity;
        }

        return $entities;
    }


    abstract public function getEntityClass(): string;


    protected function buildEntity(array $entityData): object
    {
        $nestedEntitiesKey  = $this->getDataFieldForNestedEntities();
        $nestedEntitiesData = $entityData[$nestedEntitiesKey];

        $nestedEntities = array_map(function ($row) { return $this->createNestedEntity($row); }, $nestedEntitiesData);

        return $this->createEntity($entityData, $nestedEntities);
    }


    abstract protected function getDataFieldForNestedEntities(): string;


    protected function createNestedEntity(array $entityData): object
    {
        $entityClass = $this->getNestedEntityClass();
        $entity      = $this->makeInstanceWithoutConstructor($entityClass);

        $excludeFields = $this->getNestedDbExcludeFields();
        $entityMapping = $this->getEntityFieldsMapping($this->getNestedEntityClass());

        $hydrationCallback = $this->getNestedHydrationCallback();

        $namesConverter = $this->namesConverter;

        $closure = \Closure::bind(static function ($object, $data) use ($namesConverter, $entityMapping, $excludeFields, $entityClass, $hydrationCallback) {
            foreach ($entityMapping as $dataField => $valueType) {
                if (\in_array($dataField, $excludeFields, true)) {
                    continue;
                }

                $entityField = $namesConverter->snakeCaseToCamelCase($dataField);

                if (\array_key_exists($dataField, $data) && property_exists($object, $entityField)) {
                    $object->{$entityField} = $valueType->convertToPhp($data[$dataField]);
                }
            }

            if ($hydrationCallback) {
                $closure = \Closure::bind($hydrationCallback, null, $entityClass);
                $closure->__invoke($object, $data);
            }
        }, null, $entityClass);

        $closure->__invoke($entity, $entityData);

        return $entity;
    }


    abstract public function getNestedEntityClass(): string;


    private function makeInstanceWithoutConstructor(string $entityClass): object
    {
        $reflector = new \ReflectionClass($entityClass);

        return $reflector->newInstanceWithoutConstructor();
    }


    abstract protected function getNestedDbExcludeFields(): array;


    protected function getEntityFieldsMapping(string $class): array
    {
        return $this->em->getEavSettings()->getFieldsMappingForEntityClass($class);
    }


    protected function getNestedHydrationCallback(): ?callable
    {
        return null;
    }


    protected function createEntity(array $entityData, array $nestedEntities): object
    {
        $entityClass = $this->getEntityClass();
        $entity      = $this->makeInstanceWithoutConstructor($entityClass);

        $excludeFields       = $this->getEntityDbExcludeFields();
        $entityMapping       = $this->getEntityFieldsMapping($this->getEntityClass());
        $nestedEntitiesField = $this->getEntityFieldForNestedEntities();

        $hydrationCallback = $this->getHydrationCallback();

        $namesConverter = $this->namesConverter;

        $closure = \Closure::bind(static function ($object, $data, $nestedEntities) use ($namesConverter, $entityMapping, $excludeFields, $entityClass, $nestedEntitiesField, $hydrationCallback) {
            foreach ($entityMapping as $dataField => $valueType) {
                if (\in_array($dataField, $excludeFields, true)) {
                    continue;
                }

                $entityField = $namesConverter->snakeCaseToCamelCase($dataField);

                if (\array_key_exists($dataField, $data) && property_exists($object, $entityField)) {
                    $object->{$entityField} = $valueType->convertToPhp($data[$dataField]);
                }
            }

            $object->{$nestedEntitiesField} = $nestedEntities;

            if ($hydrationCallback) {
                $closure = \Closure::bind($hydrationCallback, null, $entityClass);
                $closure->__invoke($object, $data);
            }
        }, null, $entityClass);

        $closure->__invoke($entity, $entityData, $nestedEntities);

        return $entity;
    }


    abstract protected function getEntityDbExcludeFields(): array;


    abstract protected function getEntityFieldForNestedEntities(): string;


    protected function getHydrationCallback(): ?callable
    {
        return null;
    }


    protected function removeTemporaryKeys(array $data): array
    {
        return $data;
    }


    public function extract(EAVPersistableInterface $entity): array
    {
        $entityClass       = $this->getEntityClass();
        $nestedEntityClass = $this->getNestedEntityClass();

        $excludeFields       = $this->getEntityDbExcludeFields();
        $excludeNestedFields = $this->getNestedDbExcludeFields();
        $entityMapping       = $this->getEntityFieldsMapping($this->getEntityClass());
        $nestedMapping       = $this->getEntityFieldsMapping($this->getNestedEntityClass());
        $namesConverter      = $this->namesConverter;

        $extractionCallback       = $this->getExtractionCallback();
        $nestedExtractionCallback = $this->getNestedExtractionCallback();

        $nestedEntityClosure = \Closure::bind(static function ($object) use ($namesConverter, $nestedEntityClass, $nestedMapping, $excludeNestedFields, $nestedExtractionCallback, $entity) {
            $data = [];
            /**
             * @var ValueTypeInterface $valueType
             */
            foreach ($nestedMapping as $dataField => $valueType) {
                if (\in_array($dataField, $excludeNestedFields, true)) {
                    continue;
                }

                $entityField = $namesConverter->snakeCaseToCamelCase($dataField);

                $data[$dataField] = $valueType->convertToDatabase($object->{$entityField});
            }

            if ($nestedExtractionCallback) {
                $closure = \Closure::bind($nestedExtractionCallback, null, $nestedEntityClass);
                $closure->__invoke($data, $object, $entity);
            }

            return $data;
        }, null, $nestedEntityClass);

        $entityFieldForNestedEntities = $this->getEntityFieldForNestedEntities();
        $dataFieldForNestedEntities   = $this->getDataFieldForNestedEntities();

        $closure = \Closure::bind(static function ($object) use (
            $namesConverter,
            $entityClass,
            $excludeFields,
            $entityMapping,
            $nestedEntityClosure,
            $entityFieldForNestedEntities,
            $dataFieldForNestedEntities,
            $extractionCallback
        ) {
            $data = [];

            $nestedData = array_map(static function ($value) use ($nestedEntityClosure) {
                return $nestedEntityClosure->__invoke($value);
            }, $object->{$entityFieldForNestedEntities});

            /**
             * @var ValueTypeInterface $valueType
             */
            foreach ($entityMapping as $dataField => $valueType) {
                if (\in_array($dataField, $excludeFields, true)) {
                    continue;
                }

                $entityField = $namesConverter->snakeCaseToCamelCase($dataField);

                $data[$dataField] = $valueType->convertToDatabase($object->{$entityField});
            }

            if ($extractionCallback) {
                $closure = \Closure::bind($extractionCallback, null, $entityClass);
                $closure->__invoke($data, $object);
            }

            $data[$dataFieldForNestedEntities] = $nestedData;

            return $data;

        }, null, $entityClass);

        return $closure->__invoke($entity);


    }


    protected function getExtractionCallback(): ?callable
    {
        return null;
    }


    protected function getNestedExtractionCallback(): ?callable
    {
        return null;
    }

}