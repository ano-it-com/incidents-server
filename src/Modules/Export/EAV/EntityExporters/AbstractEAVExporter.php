<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\Entity\EntityFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityType\EntityTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\Relation\EntityRelationFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelationType\Type\EntityRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

abstract class AbstractEAVExporter
{
    protected EAVEntityManagerInterface $eavEm;

    protected EAVTypeRepositoryInterface $typeRepository;

    protected EAVEntityRepositoryInterface $entityRepository;

    protected int $processedCount = 0;

    protected ?EAVType $eavType = null;

    protected EAVEntityRelationRepository $entityRelationRepository;

    protected EAVEntityRelationTypeRepository $entityRelationTypeRepository;

    protected EntityManagerInterface $em;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository
    ) {
        $this->eavEm                        = $eavEm;
        $this->entityRepository             = $entityRepository;
        $this->typeRepository               = $typeRepository;
        $this->entityRelationRepository     = $entityRelationRepository;
        $this->entityRelationTypeRepository = $entityRelationTypeRepository;
        $this->em                           = $em;
    }

    public function exportEntities(): void
    {
        $this->processedCount = 0;

        // получить все старые
        $imsEntities = $this->loadImsEntitiesById();
        // получить все новые
        $eavEntities = $this->loadEAVEntitiesByExternalId();

        // сравнить сущности
        foreach ($imsEntities as $externalId => $imsEntity) {
            $eavEntity = $eavEntities[$externalId] ?? null;
            $this->createUpdateEAVEntity($imsEntity, $eavEntity);
            $this->processedCount++;
            $this->flushByBatchSize();
        }

        foreach ($eavEntities as $externalId => $eavEntity) {
            if ( ! isset($imsEntities[$externalId])) {
                $this->deleteEAVEntity($eavEntity);
                $this->processedCount++;
                $this->flushByBatchSize();
            }
        }

        $this->flushByBatchSize($force = true);
        // получить связи по типам
        // синхронизировать связи
    }

    protected function loadImsEntitiesById(): array
    {
        $imsEntities = $this->getImsRepository()->findAll();

        return array_combine(array_map(static function (object $entity) { return $entity->getId(); }, $imsEntities), $imsEntities);
    }

    abstract protected function getImsRepository(): ServiceEntityRepositoryInterface;

    protected function loadEAVEntitiesByExternalId(): array
    {
        $eavEntities = $this->entityRepository->findBy([ (new EntityTypeFilterCriteria())->where('alias', '=', $this->getEavTypeAlias()) ], []);

        return array_combine(array_map(static function (object $entity) { return $entity->getExternalId(); }, $eavEntities), $eavEntities);
    }

    protected function createUpdateEAVEntity($imsEntity, $eavEntity): void
    {
        $type = $this->getEavType();

        if ( ! $eavEntity) {
            $eavEntity = new EAVEntity(Uuid::uuid4(), $type);
            $eavEntity->setExternalId($imsEntity->getId());

            $this->eavEm->persist($eavEntity);
        }

        $this->fillEAVEntityWithIMSEntity($type, $eavEntity, $imsEntity);
    }

    protected function getEavType(): EAVType
    {
        if ( ! $this->eavType || ($this->eavType && $this->eavType->getAlias() !== $this->getEavTypeAlias())) {
            $eavType = $this->typeRepository->findOneBy([ (new TypeFilterCriteria())->where('alias', '=', $this->getEavTypeAlias()) ]);
            if ( ! $eavType) {
                throw new \RuntimeException('EAV Type for exporter ' . __CLASS__ . ' not found!');
            }
            $this->eavType = $eavType;
        }

        return $this->eavType;
    }

    abstract protected function getEavTypeAlias(): string;

    protected function fillEAVEntityWithIMSEntity(EAVType $type, EAVEntity $eavEntity, object $imsEntity): void
    {
        $fillMapping = $this->getFillMapping();

        foreach ($fillMapping as $eavPropertyAlias => $getterCallback) {
            $propertyValues = $eavEntity->getPropertyValuesByAlias($eavPropertyAlias);

            $propertyValue = count($propertyValues) ? reset($propertyValues) : null;

            if ( ! $propertyValue) {
                $propertyValue = new EAVEntityPropertyValue(Uuid::uuid4(), $type->getPropertyByAlias($eavPropertyAlias));
                $eavEntity->addPropertyValue($propertyValue);
            }

            $propertyValue->setValue($getterCallback($imsEntity));
        }
    }

    abstract protected function getFillMapping(): array;

    protected function flushByBatchSize($force = false): void
    {
        $flushBatchSize = $this->getFlushBatchSize();

        if ($force || $this->processedCount >= $flushBatchSize) {
            $this->eavEm->flush();
            $this->eavEm->clear();
            $this->processedCount = 0;
        }
    }

    protected function getFlushBatchSize(): int
    {
        return 10;
    }

    protected function deleteEAVEntity($eavEntity): void
    {
        $this->eavEm->remove($eavEntity);
    }

    public function exportRelations(): void
    {
        $relationsMapping = $this->getRelationsMapping();

        $imsEntities = $this->loadImsEntitiesById();

        foreach ($imsEntities as $imsEntity) {
            foreach ($relationsMapping as $relationTypeAlias => $mapping) {
                $imsToGetterCallback = $mapping['getter_callback'];
                $targetEavTypeAlias  = $mapping['target_eav_type_alias'];

                $relationType     = $this->entityRelationTypeRepository->findOneBy([ (new EntityRelationTypeFilterCriteria())->where('alias', '=', $relationTypeAlias) ]);
                $targetEntityType = $this->typeRepository->findOneBy([ (new TypeFilterCriteria())->where('alias', '=', $targetEavTypeAlias) ]);

                if ( ! $relationType) {
                    throw new \InvalidArgumentException('Type with alias ' . $relationTypeAlias . ' not found');
                }

                if ( ! $targetEntityType) {
                    throw new \InvalidArgumentException('Type with alias ' . $targetEavTypeAlias . ' not found');
                }

                $eavRelationsByToId = $this->loadEAVRelationsByToId($imsEntity->getId(), $relationType);
                $imsToEntities      = $imsToGetterCallback($imsEntity);

                $this->exportRelationsForEntity($relationType, $targetEntityType, $imsEntity, $eavRelationsByToId, $imsToEntities);
            }
        }
    }

    abstract protected function getRelationsMapping(): array;

    protected function loadEAVRelationsByToId(int $externalId, EAVEntityRelationType $relationType): array
    {
        $eavEntity = $this->getEAVEntityByExternalId($externalId, $this->getEavType());

        if ( ! $eavEntity) {
            throw new \InvalidArgumentException('EAV entity with external ID ' . $externalId . ' not found!');
        }

        $relations = $this->entityRelationRepository->findBy([ (new EntityRelationFilterCriteria())->where('from_id', '=', $eavEntity->getId())->where('type_id', '=', $relationType->getId()) ]);

        return array_combine(array_map(static function (EAVEntityRelation $relation) { return $relation->getTo()->getExternalId(); }, $relations), $relations);
    }

    protected function exportRelationsForEntity(EAVEntityRelationType $relationType, EAVType $targetEntityType, object $imsEntityFrom, array $eavRelationsByToId, array $imsToEntities): void
    {
        $this->processedCount = 0;

        foreach ($imsToEntities as $imsEntityTo) {
            $imsEntityToId = $imsEntityTo->getId();

            if ( ! isset($eavRelationsByToId[$imsEntityToId])) {
                // update doesn't need, because combination of from_id, to_id, type - unique
                $this->createEAVRelation($imsEntityFrom, $imsEntityTo, $relationType, $targetEntityType);
                $this->processedCount++;
                $this->flushByBatchSize();
            }
        }

        $imsToEntitiesById = array_combine(array_map(static function (object $entity) { return $entity->getId(); }, $imsToEntities), $imsToEntities);

        /** @var EAVEntityRelation $relation */
        foreach ($eavRelationsByToId as $relation) {
            $toExternalId = $relation->getTo()->getExternalId();

            if ( ! isset($imsToEntitiesById[$toExternalId])) {
                $this->deleteEAVRelation($relation);
                $this->processedCount++;
                $this->flushByBatchSize();
            }
        }

        $this->flushByBatchSize($force = true);
    }

    private function deleteEAVRelation(EAVEntityRelation $relation): void
    {
        $this->eavEm->remove($relation);
    }

    private function createEAVRelation(object $imsEntityFrom, object $imsEntityTo, EAVEntityRelationType $relationType, EAVType $targetEntityType): void
    {
        $eavRelation = new EAVEntityRelation(Uuid::uuid4(), $relationType);

        // указать тип
        $eavFrom = $this->getEAVEntityByExternalId($imsEntityFrom->getId(), $this->getEavType());
        $eavTo   = $this->getEAVEntityByExternalId($imsEntityTo->getId(), $targetEntityType);

        if ( ! $eavFrom || ! $eavTo) {
            throw new \InvalidArgumentException('From or To EAV entity not found for relation');
        }

        $eavRelation->setTo($eavTo);
        $eavRelation->setFrom($eavFrom);

        $this->eavEm->persist($eavRelation);
    }

    private function getEAVEntityByExternalId(int $externalId, EAVType $entityType): EAVEntity
    {
        $entity = $this->entityRepository->findOneBy([ (new EntityFilterCriteria())->where('external_id', '=', $externalId)->where('type_id', '=', $entityType->getId()) ]);

        if ( ! $entity) {
            throw new \InvalidArgumentException('EAV entity with ID ' . $externalId . ' and type ' . $entityType->getAlias() . ' not found!');
        }

        return $entity;
    }
}
