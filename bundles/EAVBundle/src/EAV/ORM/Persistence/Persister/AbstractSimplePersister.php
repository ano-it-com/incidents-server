<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Builder\SimpleEntityBuilderInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

abstract class AbstractSimplePersister
{

    protected EAVEntityManagerInterface $em;

    protected CriteriaHandlerInterface $criteriaHandler;

    protected ChangesCalculatorInterface $changesCalculator;

    protected SimpleEntityBuilderInterface $builder;

    protected OrderCriteriaHandlerInterface $orderCriteriaHandler;


    abstract public static function getSupportedClass(): string;


    public function loadByCriteria(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $criteriaClassInterface = $this->getFilterCriteriaInterface();
        foreach ($criteria as $oneCriteria) {
            if ( ! is_a($oneCriteria, $criteriaClassInterface)) {
                throw new \InvalidArgumentException('Each criteria must implement ' . $criteriaClassInterface);
            }
        }

        $entityRows = $this->loadEntityRows($criteria, $orderBy, $limit, $offset);

        if ( ! count($entityRows)) {
            return [];
        }

        return $this->builder->buildEntities($entityRows);
    }


    abstract protected function getFilterCriteriaInterface(): string;


    protected function loadEntityRows(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($tableName)
                       ->select([ $tableName . '.*' ])
                       ->groupBy($tableName . '.id');

        $this->criteriaHandler->applyCriteria($qb, $criteria, $this->em->getEavSettings());

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        $this->orderCriteriaHandler->applyOrdering($qb, $orderBy, $this->em->getEavSettings());

        $stmt = $qb->execute();

        $sql = $qb->getSQL();

        $params = $qb->getParameters();

        return $stmt->fetchAll();
    }


    abstract protected function getEntityType(): string;


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array
    {
        $newValues = $this->builder->extractData($entity);

        return $this->changesCalculator->getChanges($newValues, $oldValues);
    }


    public function insert(EAVPersistableInterface $entity): void
    {
        $entityData = $this->builder->extractData($entity);

        $this->doInsert($entityData);
    }


    protected function doInsert(array $entityData): void
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $this->em->getConnection()->insert($tableName, $entityData);

    }


    public function delete(EAVPersistableInterface $entity): void
    {
        $entityData = $this->builder->extractData($entity);

        $this->doDelete($entityData);
    }


    protected function doDelete(array $entityData): void
    {
        $entityTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $this->em->getConnection()->delete($entityTableName, [ 'id' => $entityData['id'] ]);

    }


    public function update(EAVPersistableInterface $entity, array $changeSet): void
    {
        $entityChanges = $changeSet['entity'] ?? [];

        if (count($entityChanges)) {
            $this->doUpdate($entity, $entityChanges);
        }

    }


    protected function doUpdate(EAVPersistableInterface $entity, array $entityChanges): void
    {
        $entityTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $values = [];
        foreach ($entityChanges as $field => $change) {
            $newValue = $change['new'];

            $values[$field] = $newValue;
        }

        $this->em->getConnection()->update($entityTableName, $values, [ 'id' => $entity->getId() ]);


    }


    public function getCurrentState(EAVPersistableInterface $entity): array
    {
        return $this->builder->extractData($entity);
    }
}