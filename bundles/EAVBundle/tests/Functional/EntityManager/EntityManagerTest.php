<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\EntityManager;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\Entity\EntityFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\Relation\EntityRelationFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelationType\Type\EntityRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelation\Relation\TypePropertyRelationFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypePropertyRelationType\Type\TypePropertyRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelation\Relation\TypeRelationFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelationType\Type\TypeRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypePropertyRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypePropertyRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypePropertyRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use Ramsey\Uuid\Uuid;

class EntityManagerTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    protected $em;

    /** @var EAVTypeRepository */
    protected $typeRepository;

    /** @var EAVEntityRepository */
    protected $entityRepository;

    /** @var EAVEntityRelationTypeRepository */
    protected $relationTypeRepository;

    /** @var EAVEntityRelationRepository */
    protected $relationRepository;

    /** @var EAVTypeRelationRepository */
    protected $typeRelationRepository;

    /** @var EAVTypeRelationTypeRepository */
    protected $typeRelationTypeRepository;

    /** @var EAVTypePropertyRelationTypeRepository */
    protected $typePropertyRelationTypeRepository;

    /** @var EAVTypePropertyRelationRepository */
    protected $typePropertyRelationRepository;


    public function testEmReturnsNewTypeAfterClear(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typesFromDb = $this->typeRepository->findBy([ (new TypeFilterCriteria())->where('id', '=', $type->getId()) ]);

        self::assertCount(1, $typesFromDb);

        $typeFromDb = reset($typesFromDb);

        self::assertInstanceOf(EAVType::class, $typeFromDb);
        self::assertEquals($type->getId(), $typeFromDb->getId());
        self::assertNotSame($type, $typeFromDb);

    }


    private function createType(): EAVType
    {
        $type = new EAVType(Uuid::uuid4()->toString());
        $type->setTitle('title');
        $type->setAlias('alias');

        return $type;
    }


    public function testEmReturnsNewEntityAfterClear(): void
    {
        $type   = $this->createType();
        $entity = $this->createEntity($type);

        $this->em->persist($type);
        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->entityRepository->findBy([ (new EntityFilterCriteria())->where('id', '=', $entity->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntity::class, $entityFromDb);
        self::assertEquals($entity->getId(), $entityFromDb->getId());
        self::assertNotSame($entity, $entityFromDb);

    }


    private function createEntity(EAVType $type): EAVEntity
    {
        $entity = new EAVEntity(Uuid::uuid4()->toString(), $type);

        return $entity;
    }


    public function testEmReturnsNewRelationTypeAfterClear(): void
    {
        $relationType = $this->createRelationType();

        $this->em->persist($relationType);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->relationTypeRepository->findBy([ (new EntityRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntityRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertNotSame($relationType, $entityFromDb);

    }


    private function createRelationType(): EAVEntityRelationType
    {
        $relationType = new EAVEntityRelationType(Uuid::uuid4());
        $relationType->setAlias('alias');
        $relationType->setTitle('title');

        return $relationType;
    }


    public function testEmReturnsNewRelationAfterClear(): void
    {
        $relation = $this->createRelationAndPersist();

        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->relationRepository->findBy([ (new EntityRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntityRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertNotSame($relation, $entityFromDb);

    }


    private function createRelationAndPersist(): EAVEntityRelation
    {
        // создаем тип
        $type = $this->createType();
        $this->em->persist($type);
        // создаем объекты
        $entity1 = $this->createEntity($type);
        $entity2 = $this->createEntity($type);
        $this->em->persist($entity1);
        $this->em->persist($entity2);
        // создаем тип релейшана
        $relationType = $this->createRelationType();
        $this->em->persist($relationType);
        // создаем релейшан

        $relation = new EAVEntityRelation(Uuid::uuid4(), $relationType);
        $relation->setFrom($entity1);
        $relation->setTo($entity2);

        $this->em->persist($relation);

        return $relation;
    }


    public function testEmReturnsNewTypeRelationTypeAfterClear(): void
    {
        $relationType = $this->createTypeRelationType();

        $this->em->persist($relationType);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->typeRelationTypeRepository->findBy([ (new TypeRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypeRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertNotSame($relationType, $entityFromDb);

    }


    //---------------


    private function createTypeRelationType(): EAVTypeRelationType
    {
        $relationType = new EAVTypeRelationType(Uuid::uuid4());
        $relationType->setAlias('alias');
        $relationType->setTitle('title');

        return $relationType;
    }


    public function testEmReturnsNewTypeRelationAfterClear(): void
    {
        $relation = $this->createTypeRelationAndPersist();

        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->typeRelationRepository->findBy([ (new TypeRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypeRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertNotSame($relation, $entityFromDb);

    }


    private function createTypeRelationAndPersist(): EAVTypeRelation
    {
        // создаем тип
        $type1 = $this->createType();
        $this->em->persist($type1);
        // создаем тип
        $type2 = $this->createType();
        $this->em->persist($type2);

        // создаем тип релейшана
        $relationType = $this->createTypeRelationType();
        $this->em->persist($relationType);
        // создаем релейшан

        $relation = new EAVTypeRelation(Uuid::uuid4(), $relationType);
        $relation->setFrom($type1);
        $relation->setTo($type2);

        $this->em->persist($relation);

        return $relation;
    }


    public function testEmReturnsNewTypePropertyRelationTypeAfterClear(): void
    {
        $relationType = $this->createTypePropertyRelationType();

        $this->em->persist($relationType);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->typePropertyRelationTypeRepository->findBy([ (new TypePropertyRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypePropertyRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertNotSame($relationType, $entityFromDb);

    }


    private function createTypePropertyRelationType(): EAVTypePropertyRelationType
    {
        $relationType = new EAVTypePropertyRelationType(Uuid::uuid4());
        $relationType->setAlias('alias');
        $relationType->setTitle('title');

        return $relationType;
    }


    public function testEmReturnsNewTypePropertyRelationAfterClear(): void
    {
        $relation = $this->createTypePropertyRelationAndPersist();

        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->typePropertyRelationRepository->findBy([ (new TypePropertyRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypePropertyRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertNotSame($relation, $entityFromDb);

    }


    private function createTypePropertyRelationAndPersist(): EAVTypePropertyRelation
    {
        $relationType = $this->createTypePropertyRelationType();
        $type1        = $this->createTypeWithOneProperty();
        $type2        = $this->createTypeWithOneProperty();

        $this->em->persist($relationType);
        $this->em->persist($type1);
        $this->em->persist($type2);

        $relation = new EAVTypePropertyRelation(Uuid::uuid4(), $relationType);
        $relation->setFrom($type1->getProperties()[0]);
        $relation->setTo($type2->getProperties()[0]);

        $this->em->persist($relation);

        return $relation;
    }


    private function createTypeWithOneProperty(): EAVType
    {
        $type = new EAVType(Uuid::uuid4()->toString());
        $type->setTitle('title');
        $type->setAlias('alias');

        $property = new EAVTypeProperty(Uuid::uuid4(), $type, new TextType());
        $property->setAlias('property_alias_1');
        $property->setTitle('property_title_1');

        $type->setProperties([ $property ]);

        return $type;
    }


    public function testEmReturnsOldTypeWithoutClear(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();

        $typesFromDb = $this->typeRepository->findBy([ (new TypeFilterCriteria())->where('id', '=', $type->getId()) ]);

        self::assertCount(1, $typesFromDb);

        $typeFromDb = reset($typesFromDb);

        self::assertInstanceOf(EAVType::class, $typeFromDb);
        self::assertEquals($type->getId(), $typeFromDb->getId());
        self::assertSame($type, $typeFromDb);

    }


    public function testEmReturnsOldEntityWithoutClear(): void
    {
        $type   = $this->createType();
        $entity = $this->createEntity($type);

        $this->em->persist($type);
        $this->em->persist($entity);
        $this->em->flush();

        $entitiesFromDb = $this->entityRepository->findBy([ (new EntityFilterCriteria())->where('id', '=', $entity->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntity::class, $entityFromDb);
        self::assertEquals($entity->getId(), $entityFromDb->getId());
        self::assertSame($entity, $entityFromDb);

    }


    public function testEmReturnsOldRelationTypeWithoutClear(): void
    {
        $relationType = $this->createRelationType();

        $this->em->persist($relationType);
        $this->em->flush();

        $entitiesFromDb = $this->relationTypeRepository->findBy([ (new EntityRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntityRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertSame($relationType, $entityFromDb);

    }


    public function testEmReturnsOldRelationWithoutClear(): void
    {
        $relation = $this->createRelationAndPersist();

        $this->em->flush();

        $entitiesFromDb = $this->relationRepository->findBy([ (new EntityRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVEntityRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertSame($relation, $entityFromDb);

    }


    public function testEmReturnsOldTypeRelationTypeWithoutClear(): void
    {
        $relationType = $this->createTypeRelationType();

        $this->em->persist($relationType);
        $this->em->flush();

        $entitiesFromDb = $this->typeRelationTypeRepository->findBy([ (new TypeRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypeRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertSame($relationType, $entityFromDb);

    }


    public function testEmReturnsOldTypeRelationWithoutClear(): void
    {
        $relation = $this->createTypeRelationAndPersist();

        $this->em->flush();

        $entitiesFromDb = $this->typeRelationRepository->findBy([ (new TypeRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypeRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertSame($relation, $entityFromDb);

    }


    public function testEmReturnsOldTypePropertyRelationTypeWithoutClear(): void
    {
        $relationType = $this->createTypePropertyRelationType();

        $this->em->persist($relationType);
        $this->em->flush();

        $entitiesFromDb = $this->typePropertyRelationTypeRepository->findBy([ (new TypePropertyRelationTypeFilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypePropertyRelationType::class, $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertSame($relationType, $entityFromDb);

    }


    public function testEmReturnsOldTypePropertyRelationWithoutClear(): void
    {
        $relation = $this->createTypePropertyRelationAndPersist();

        $this->em->flush();

        $entitiesFromDb = $this->typePropertyRelationRepository->findBy([ (new TypePropertyRelationFilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf(EAVTypePropertyRelation::class, $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertSame($relation, $entityFromDb);

    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                                 = self::$container->get(EAVEntityManager::class);
        $this->entityRepository                   = self::$container->get(EAVEntityRepository::class);
        $this->typeRepository                     = self::$container->get(EAVTypeRepository::class);
        $this->relationTypeRepository             = self::$container->get(EAVEntityRelationTypeRepository::class);
        $this->relationRepository                 = self::$container->get(EAVEntityRelationRepository::class);
        $this->typeRelationRepository             = self::$container->get(EAVTypeRelationRepository::class);
        $this->typeRelationTypeRepository         = self::$container->get(EAVTypeRelationTypeRepository::class);
        $this->typePropertyRelationTypeRepository = self::$container->get(EAVTypePropertyRelationTypeRepository::class);
        $this->typePropertyRelationRepository     = self::$container->get(EAVTypePropertyRelationRepository::class);
    }
}