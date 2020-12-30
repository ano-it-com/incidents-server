<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\Type\TypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class TypePersistenceTest extends BundleWithPostgresTestCase
{

    private $em;

    private $typeRepository;

    private $valueTypes;


    public function testPersistTypeWithProperties(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $this->compareTwoTypes($type, $typeFromDb);
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


    private function getFromDbById(string $id): ?EAVType
    {
        /** @var EAVType[] $typesFromDb */
        $typesFromDb = $this->typeRepository->findBy([ (new TypeFilterCriteria())->where('id', '=', $id) ]);

        return count($typesFromDb) ? $typesFromDb[0] : null;
    }


    private function compareTwoTypes(EAVType $type1, EAVType $type2): void
    {
        self::assertNotSame($type1, $type2);
        self::assertEquals($type1->getId(), $type2->getId());
        self::assertEquals($type1->getAlias(), $type2->getAlias());
        self::assertEquals($type1->getTitle(), $type2->getTitle());

        /** @var ValueTypeInterface $valueType */
        $valueType = $this->valueTypes->getByClass(BasicJsonMetaType::class);
        self::assertTrue($valueType->isEqualDBValues($type1->getMeta()->toString(), $type2->getMeta()->toString()));

        //test properties
        $propertiesFromDb = $type2->getProperties();
        $propertiesFromDb = array_combine(array_map(function (EAVTypeProperty $property) { return $property->getId(); }, $propertiesFromDb), $propertiesFromDb);

        self::assertSameSize($type1->getProperties(), $propertiesFromDb);

        /**
         * @var string          $id
         * @var EAVTypeProperty $property
         */
        foreach ($type1->getProperties() as $property) {
            $propertyFromDB = $propertiesFromDb[$property->getId()] ?? null;
            self::assertNotNull($propertyFromDB);

            self::assertEquals($property->getId(), $propertyFromDB->getId());
            self::assertEquals($property->getTitle(), $propertyFromDB->getTitle());
            self::assertEquals($property->getAlias(), $propertyFromDB->getAlias());
            self::assertEquals($property->getTypeId(), $propertyFromDB->getTypeId());
            self::assertEquals($property->getValueType()->getCode(), $propertyFromDB->getValueType()->getCode());
        }
    }


    public function testUpdateTypeWithProperties(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $typeFromDb->setTitle($typeFromDb->getTitle() . '_updated');
        $typeFromDb->setAlias($typeFromDb->getAlias() . '_updated');

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeFromDb->setMeta($basicMeta);

        foreach ($typeFromDb->getProperties() as $property) {
            $property->setAlias($property->getAlias() . '_updated');
            $property->setTitle($property->getAlias() . '_updated');
            $property->setMeta($basicMeta);
        }

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);


    }


    public function testDeleteTypeWithProperties(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $this->em->remove($typeFromDb);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        self::assertNull($typeFromDb2);
    }


    public function testAddDeleteChangeProperties(): void
    {
        $type = $this->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $properties = $type->getProperties();

        // deleted
        $deletedProperty = array_shift($properties);

        // new
        $newProperty = new EAVTypeProperty(Uuid::uuid4()->toString(), $type, new TextType());
        $newProperty->setAlias('property_alias');
        $newProperty->setTitle('property_title');
        $properties[] = $newProperty;

        //updated
        /** @var EAVTypeProperty $propToChange */
        $propToChange = reset($properties);
        $propToChange->setTitle('updated');
        $propToChange->setAlias('updated');

        $typeFromDb->setProperties($properties);

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em             = self::$container->get(EAVEntityManager::class);
        $this->typeRepository = self::$container->get(EAVTypeRepository::class);
        $this->valueTypes     = self::$container->get(ValueTypes::class);
    }
}