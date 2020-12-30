<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVUnitOfWork implements EAVUnitOfWorkInterface
{

    public const STATE_MANAGED = 0;
    public const STATE_NEW = 1;
    public const STATE_REMOVED = 2;

    private array $entityStates = [];

    private array $originalEntityData = [];

    private array $entityChangeSets = [];

    private array $identityMap = [];

    /**
     * @var EAVPersistableInterface[]
     */
    private array $toInsert = [];

    /**
     * @var EAVPersistableInterface[]
     */
    private array $toUpdate = [];

    /**
     * @var EAVPersistableInterface[]
     */
    private array $toDelete = [];

    private EAVEntityManagerInterface $em;

    private EAVPersistersFactoryInterface $persistersFactory;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                = $em;
        $this->persistersFactory = $persistersFactory;
    }


    public function persist(EAVPersistableInterface $entity): void
    {
        $entityState = $this->getEntityState($entity);

        switch ($entityState) {
            case self::STATE_NEW:
                $this->persistNew($entity);
                break;
            case self::STATE_REMOVED:
                $this->returnToManagedFromDeleted($entity);
                break;
        }
    }


    private function getEntityState(EAVPersistableInterface $entity): int
    {
        $oid = spl_object_hash($entity);

        return $this->entityStates[$oid] ?? self::STATE_NEW;
    }


    private function persistNew(EAVPersistableInterface $entity): void
    {
        $oid = spl_object_hash($entity);

        $this->entityStates[$oid] = self::STATE_MANAGED;
        $this->addToInsert($entity);
    }


    private function addToInsert(EAVPersistableInterface $entity): void
    {
        $oid = spl_object_hash($entity);

        if (isset($this->toUpdate[$oid])) {
            throw new \InvalidArgumentException("Entity is already in update list");
        }

        if (isset($this->toDelete[$oid])) {
            throw new \InvalidArgumentException("Entity is already in delete list");
        }
        if (isset($this->originalEntityData[$oid]) && ! isset($this->toInsert[$oid])) {
            throw new \InvalidArgumentException("Entity is already managed");
        }

        if (isset($this->entityInsertions[$oid])) {
            throw new \InvalidArgumentException("Entity is already in insert list");
        }

        $this->toInsert[$oid] = $entity;

        $this->addToIdentityMap($entity);

    }


    private function addToIdentityMap(EAVPersistableInterface $entity): void
    {
        $entityClass = get_class($entity);
        $entityId    = $entity->getId();

        if ( ! $entityId) {
            throw new \InvalidArgumentException('Cannot add entity without identifier to IM');
        }

        if ( ! isset($this->identityMap[$entityClass][$entityId])) {
            $this->identityMap[$entityClass][$entityId] = $entity;
        }

    }


    private function returnToManagedFromDeleted(EAVPersistableInterface $entity): void
    {
        $oid = spl_object_hash($entity);

        unset($this->toDelete[$oid]);

        $this->addToIdentityMap($entity);

        $this->entityStates[$oid] = self::STATE_MANAGED;

    }


    public function commit(): void
    {
        $this->computeChangeSets();

        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        // TODO cache class to persister mapping
        try {
            foreach ($this->toUpdate as $oid => $entity) {
                $changeSet = $this->entityChangeSets[$oid];

                $persister = $this->getPersisterForClass(get_class($entity));

                $persister->update($entity, $changeSet);

                $this->registerManaged($entity, $persister->getCurrentState($entity));

            }

            foreach ($this->toInsert as $oid => $entity) {
                $persister = $this->getPersisterForClass(get_class($entity));

                $persister->insert($entity);

                $this->registerManaged($entity, $persister->getCurrentState($entity));
            }

            foreach ($this->toDelete as $oid => $entity) {
                $persister = $this->getPersisterForClass(get_class($entity));

                $persister->delete($entity);

            }

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();

            throw $e;
        }

        $this->clearAfterCommit();

    }


    private function computeChangeSets(): void
    {
        foreach ($this->identityMap as $entityClass => $entities) {

            foreach ($entities as $entityId => $entity) {
                $oid         = spl_object_hash($entity);
                $entityState = $this->getEntityState($entity);

                if ($entityState === self::STATE_REMOVED && isset($this->toDelete[$oid])) {
                    continue;
                }
                if ($entityState === self::STATE_MANAGED && isset($this->toInsert[$oid])) {
                    continue;
                }

                if ($entityState === self::STATE_MANAGED && ! isset($this->toInsert[$oid])) {
                    $persister = $this->persistersFactory->getForClass(get_class($entity), $this->em);

                    $changes = $this->computeChanges($entity, $persister);

                    if (count($changes)) {
                        $this->entityChangeSets[$oid] = $changes;
                        $this->toUpdate[$oid]         = $entity;
                    }
                }
            }
        }
    }


    private function computeChanges(EAVPersistableInterface $entity, EAVPersisterInterface $persister): array
    {
        $oid = spl_object_hash($entity);

        $originalData = $this->originalEntityData[$oid];

        return $persister->getChanges($entity, $originalData);
    }


    public function getPersisterForClass(string $class): EAVPersisterInterface
    {
        return $this->persistersFactory->getForClass($class, $this->em);
    }


    public function registerManaged($entity, array $data): void
    {
        $oid = spl_object_hash($entity);

        $this->entityStates[$oid]       = self::STATE_MANAGED;
        $this->originalEntityData[$oid] = $data;

        $this->addToIdentityMap($entity);
    }


    private function clearAfterCommit(): void
    {
        foreach ($this->entityStates as $oid => $state) {
            if ($state === self::STATE_REMOVED) {
                unset($this->entityStates[$oid], $this->originalEntityData[$oid]);
            }
        }

        $this->toDelete         = [];
        $this->toUpdate         = [];
        $this->toInsert         = [];
        $this->entityChangeSets = [];

    }


    public function remove(EAVPersistableInterface $entity): void
    {
        $entityState = $this->getEntityState($entity);

        if ($entityState !== self::STATE_MANAGED) {
            return;
        }

        $this->addToRemove($entity);
    }


    private function addToRemove(EAVPersistableInterface $entity): void
    {
        $oid = spl_object_hash($entity);

        unset($this->toInsert[$oid], $this->toUpdate[$oid]);

        $this->removeFromIdentityMap($entity);

        $this->toDelete[$oid] = $entity;

        $this->entityStates[$oid] = self::STATE_REMOVED;
    }


    private function removeFromIdentityMap(EAVPersistableInterface $entity): void
    {
        $entityClass = get_class($entity);
        $entityId    = $entity->getId();

        if ( ! $entityId) {
            throw new \InvalidArgumentException('Cannot remove entity without identifier from IM');
        }

        unset($this->identityMap[$entityClass][$entityId]);
    }


    public function get(string $entityClass, string $id): ?EAVPersistableInterface
    {
        return $this->identityMap[$entityClass][$id] ?? null;
    }


    public function has(string $entityClass, string $id): bool
    {
        return isset($this->identityMap[$entityClass][$id]);
    }


    public function clear(): void
    {
        $this->entityStates =
        $this->originalEntityData =
        $this->entityChangeSets =
        $this->identityMap =
        $this->toInsert =
        $this->toUpdate =
        $this->toDelete = [];
    }

}