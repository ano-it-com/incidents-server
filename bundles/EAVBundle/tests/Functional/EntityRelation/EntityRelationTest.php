<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityRelation\Relation\EntityRelationFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\RelationTypeRestrictions;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class EntityRelationTest extends BundleWithPostgresTestCase
{

    private $em;

    private $relationRepository;

    private $valueTypes;


    public function testPersistRelation(): void
    {
        $relation = $this->createRelationAndSaveToDB();

        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $this->compareTwoRelations($relation, $relationFromDb);
    }


    private function createRelationAndSaveToDB(): EAVEntityRelation
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

        $date      = new DateTime('2020-01-01');
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $relation = new EAVEntityRelation(Uuid::uuid4(), $relationType);
        $relation->setFrom($entity1);
        $relation->setTo($entity2);
        $relation->setMeta($basicMeta);

        $this->em->persist($relation);

        $this->em->flush();

        return $relation;
    }


    private function createType(): EAVType
    {
        $date = new \DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $type = new EAVType(Uuid::uuid4()->toString());
        $type->setAlias('alias');
        $type->setTitle('title');
        $type->setMeta($basicMeta);

        $properties = [];

        for ($i = 0; $i <= 5; $i++) {
            $prop = new EAVTypeProperty(Uuid::uuid4()->toString(), $type, new TextType());
            $prop->setAlias('property_alias_' . $i);
            $prop->setTitle('property_title_' . $i);
            $prop->setMeta($basicMeta);

            $properties[] = $prop;
        }

        $type->setProperties(array_values($properties));

        return $type;
    }


    private function createEntity(EAVType $type): EAVEntity
    {
        $date      = new \DateTime('2020-01-01');
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $entity = new EAVEntity(Uuid::uuid4(),$type);

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


    private function createRelationType(): EAVEntityRelationType
    {
        $date = new DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $restriction = new EAVEntityRelationTypeRestriction(Uuid::uuid4());
        $restriction->setRestrictionTypeCode('entity_type_restriction');
        $restriction->setRestriction([
            RelationTypeRestrictions::FROM => [
                'Person'
            ],
            RelationTypeRestrictions::TO   => [
                'Car',
                'Boat',
            ],
        ]);
        $restriction->setMeta($basicMeta);

        $relationType = new EAVEntityRelationType(Uuid::uuid4());
        $relationType->setAlias('alias');
        $relationType->setTitle('title');
        $relationType->setRestrictions([ $restriction ]);
        $relationType->setMeta($basicMeta);

        return $relationType;
    }


    private function getFromDbById(string $id): ?EAVEntityRelation
    {
        /** @var EAVEntityRelation[] $typesFromDb */
        $relationsFromDb = $this->relationRepository->findBy([ (new EntityRelationFilterCriteria())->where('id', '=', $id) ]);

        return count($relationsFromDb) ? $relationsFromDb[0] : null;
    }


    private function compareTwoRelations(EAVEntityRelation $relation1, EAVEntityRelation $relation2): void
    {
        self::assertNotSame($relation1, $relation2);
        self::assertEquals($relation1->getId(), $relation2->getId());
        self::assertEquals($relation1->getType()->getId(), $relation2->getType()->getId());
        self::assertEquals($relation1->getFrom()->getId(), $relation2->getFrom()->getId());
        self::assertEquals($relation1->getTo()->getId(), $relation2->getTo()->getId());

        /** @var ValueTypeInterface $valueType */
        $valueType = $this->valueTypes->getByClass(BasicJsonMetaType::class);
        self::assertTrue($valueType->isEqualDBValues($relation1->getMeta()->toString(), $relation2->getMeta()->toString()));
    }


    public function testUpdateRelation(): void
    {
        $relation = $this->createRelationAndSaveToDB();

        $newType         = $this->createType();
        $newRelationType = $this->createRelationType();
        $newObject1      = $this->createEntity($newType);
        $newObject2      = $this->createEntity($newType);

        $this->em->persist($newType);
        $this->em->persist($newRelationType);
        $this->em->persist($newObject1);
        $this->em->persist($newObject2);

        $this->em->flush();
        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $relationFromDb->setFrom($newObject1);
        $relationFromDb->setTo($newObject2);

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $relationFromDb->setMeta($basicMeta);

        $this->em->flush();
        $this->em->clear();

        $relationFromDb2 = $this->getFromDbById($relationFromDb->getId());

        $this->compareTwoRelations($relationFromDb, $relationFromDb2);


    }


    public function testDeleteRelation(): void
    {
        $relation = $this->createRelationAndSaveToDB();

        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $this->em->remove($relationFromDb);
        $this->em->flush();
        $this->em->clear();

        $relationFromDb2 = $this->getFromDbById($relationFromDb->getId());

        self::assertNull($relationFromDb2);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                 = self::$container->get(EAVEntityManager::class);
        $this->relationRepository = self::$container->get(EAVEntityRelationRepository::class);
        $this->valueTypes         = self::$container->get(ValueTypes::class);
    }

}