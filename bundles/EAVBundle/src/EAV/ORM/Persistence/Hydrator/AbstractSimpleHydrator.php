<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

abstract class AbstractSimpleHydrator
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
                $entity = $this->createEntity($entityData);
                $uow->registerManaged($entity, $this->removeTemporaryKeys($entityData));
            }

            $entities[] = $entity;
        }

        return $entities;
    }


    abstract public function getEntityClass(): string;


    protected function createEntity(array $entityData): object
    {
        $entityClass = $this->getEntityClass();
        $entity      = $this->makeInstanceWithoutConstructor($entityClass);

        $excludeFields = $this->getDbExcludeFields();
        $entityMapping = $this->getEntityFieldsMapping();

        $hydrationCallback = $this->getHydrationCallback();

        $namesConverter = $this->namesConverter;

        $closure = \Closure::bind(static function ($object, $data) use ($namesConverter, $entityMapping, $excludeFields, $entityClass, $hydrationCallback) {
            /**
             * @var ValueTypeInterface $valueType
             */
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


    private function makeInstanceWithoutConstructor(string $entityClass): object
    {
        $reflector = new \ReflectionClass($entityClass);

        return $reflector->newInstanceWithoutConstructor();
    }


    abstract protected function getDbExcludeFields(): array;


    protected function getEntityFieldsMapping(): array
    {
        return $this->em->getEavSettings()->getFieldsMappingForEntityClass($this->getEntityClass());
    }


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
        $entityClass = $this->getEntityClass();

        $excludeFields  = $this->getDbExcludeFields();
        $entityMapping  = $this->getEntityFieldsMapping();
        $namesConverter = $this->namesConverter;

        $extractionCallback = $this->getExtractionCallback();

        $closure = \Closure::bind(static function ($object) use ($namesConverter, $entityClass, $entityMapping, $excludeFields, $extractionCallback) {
            $data = [];
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

            return $data;

        }, null, $entityClass);

        return $closure->__invoke($entity);


    }


    protected function getExtractionCallback(): ?callable
    {
        return null;
    }

}