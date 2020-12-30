<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\Entity\EntityFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class EntityPersistenceTest extends BundleWithPostgresTestCase
{

    private $em;

    private $entityRepository;

    private $typeRepository;

    private $valueTypes;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $em = self::$container->get(EAVEntityManager::class);
        self::createTypes($em);
    }


    private static function createTypes(EAVEntityManager $em): void
    {
        $date = new \DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        for ($j = 0; $j < 2; $j++) {
            $type = new EAVType(Uuid::uuid4()->toString());
            $type->setAlias('alias_' . $j);
            $type->setTitle('title_' . $j);
            $type->setMeta($basicMeta);

            $properties = [];

            for ($i = 0; $i <= 5; $i++) {
                $prop = new EAVTypeProperty(Uuid::uuid4()->toString(), $type, new TextType());
                $prop->setAlias('property_' . $j . '_alias_' . $i);
                $prop->setTitle('property_' . $j . '_title_' . $i);
                $prop->setMeta($basicMeta);

                $properties[] = $prop;
            }

            $type->setProperties(array_values($properties));
            $em->persist($type);
        }

        $em->flush();
        $em->clear();
    }


    public function testPersistEntityWithValues(): void
    {
        $types = $this->typeRepository->findBy([]);

        $entity = $this->createEntity($types[0]);

        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $this->compareTwoEntities($entity, $entityFromDb);
    }


    private function createEntity(EAVType $type): EAVEntity
    {
        $date      = new \DateTime('2020-01-01');
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $entity = new EAVEntity(Uuid::uuid4(), $type);

        $entity->setMeta($basicMeta);
        $values = [];
        foreach ($type->getProperties() as $property) {
            $value       = $property->getAlias() . '_value';
            $entityValue = new EAVEntityPropertyValue(Uuid::uuid4(), $property);
            $entityValue->setValue($value);
            $entityValue->setMeta($basicMeta);
            $values[] = $entityValue;
        }

        $entity->setValues($values);

        return $entity;

    }


    private function getFromDbById(string $id): ?EAVEntity
    {
        /** @var EAVEntity[] $entitiesFromDb */
        $entitiesFromDb = $this->entityRepository->findBy([ (new EntityFilterCriteria())->where('id', '=', $id) ]);

        return count($entitiesFromDb) ? $entitiesFromDb[0] : null;
    }


    private function compareTwoEntities(EAVEntity $entity1, EAVEntity $entity2): void
    {
        self::assertNotSame($entity1, $entity2);
        self::assertEquals($entity1->getId(), $entity2->getId());
        self::assertEquals($entity1->getType()->getId(), $entity2->getType()->getId());

        /** @var ValueTypeInterface $valueType */
        $valueType = $this->valueTypes->getByClass(BasicJsonMetaType::class);
        self::assertTrue($valueType->isEqualDBValues($entity1->getMeta()->toString(), $entity2->getMeta()->toString()));

        //test values
        $valuesFromDb = $entity2->getValues();
        $valuesFromDb = array_combine(array_map(function (EAVEntityPropertyValue $value) { return $value->getId(); }, $valuesFromDb), $valuesFromDb);

        self::assertSameSize($entity1->getValues(), $valuesFromDb);

        /**
         * @var string                  $id
         * @var  EAVEntityPropertyValue $value
         */
        foreach ($entity1->getValues() as $value) {
            $valueFromDB = $valuesFromDb[$value->getId()] ?? null;
            self::assertNotNull($valueFromDB);

            self::assertEquals($value->getId(), $valueFromDB->getId());
            self::assertEquals($value->getTypePropertyId(), $valueFromDB->getTypePropertyId());
            self::assertEquals($value->getValue(), $valueFromDB->getValue());
            self::assertEquals($value->getValueTypeCode(), $valueFromDB->getValueTypeCode());
        }
    }


    public function testUpdateEntityWithValues(): void
    {
        $types = $this->typeRepository->findBy([]);

        $entity = $this->createEntity($types[0]);

        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $entityFromDb->setMeta($basicMeta);

        foreach ($entityFromDb->getValues() as $value) {
            $value->setValue($value->getValue() . '_updated');
        }

        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        $this->compareTwoEntities($entityFromDb, $entityFromDb2);


    }


    public function testDeleteEntityWithProperties(): void
    {
        $types = $this->typeRepository->findBy([]);

        $entity = $this->createEntity($types[0]);

        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $this->em->remove($entityFromDb);
        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        self::assertNull($entityFromDb2);
    }


    public function testAddDeleteChangeValues(): void
    {
        $types = $this->typeRepository->findBy([]);

        $entity = $this->createEntity($types[0]);

        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $values = $entityFromDb->getValues();

        // deleted
        $deletedValue = array_shift($values);

        $deletedPropertyType = null;
        $type                = $entity->getType();
        $property            = $type->getPropertyById($deletedValue->getTypePropertyId());

        // new
        $newValue = new EAVEntityPropertyValue(Uuid::uuid4(), $property);
        $newValue->setValue('new value');

        $values[] = $newValue;

        //updated
        /** @var EAVEntityPropertyValue $valueToChange */
        $valueToChange = reset($values);
        $valueToChange->setValue('updated');

        $entityFromDb->setValues($values);

        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        $this->compareTwoEntities($entityFromDb, $entityFromDb2);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em               = self::$container->get(EAVEntityManager::class);
        $this->entityRepository = self::$container->get(EAVEntityRepository::class);
        $this->typeRepository   = self::$container->get(EAVTypeRepository::class);
        $this->valueTypes       = self::$container->get(ValueTypes::class);
    }
}