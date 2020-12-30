<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\TypeRelationType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeRelationType\Type\TypeRelationTypeFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\RelationTypeRestrictions;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeRelationTypeRestriction;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRelationTypeRepository;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class TypeRelationTypeTest extends BundleWithPostgresTestCase
{

    private $em;

    private $relationTypeRepository;

    private $valueTypes;


    public function testPersistTypeWithRestrictions(): void
    {
        $type = $this->createRelationType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $this->compareTwoTypes($type, $typeFromDb);
    }


    private function createRelationType(): EAVTypeRelationType
    {
        $date = new DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $restriction = new EAVTypeRelationTypeRestriction(Uuid::uuid4());
        $restriction->setRestrictionTypeCode('type_type_restriction');
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

        $relationType = new EAVTypeRelationType(Uuid::uuid4());
        $relationType->setAlias('alias');
        $relationType->setTitle('title');
        $relationType->setRestrictions([ $restriction ]);
        $relationType->setMeta($basicMeta);

        return $relationType;
    }


    private function getFromDbById(string $id): ?EAVTypeRelationType
    {
        /** @var EAVTypeRelationType[] $typesFromDb */
        $typesFromDb = $this->relationTypeRepository->findBy([ (new TypeRelationTypeFilterCriteria())->where('id', '=', $id) ]);

        return count($typesFromDb) ? $typesFromDb[0] : null;
    }


    private function compareTwoTypes(EAVTypeRelationType $type1, EAVTypeRelationType $type2): void
    {
        self::assertNotSame($type1, $type2);
        self::assertEquals($type1->getId(), $type2->getId());
        self::assertEquals($type1->getAlias(), $type2->getAlias());
        self::assertEquals($type1->getTitle(), $type2->getTitle());

        /** @var ValueTypeInterface $valueType */
        $valueType = $this->valueTypes->getByClass(BasicJsonMetaType::class);
        self::assertTrue($valueType->isEqualDBValues($type1->getMeta()->toString(), $type2->getMeta()->toString()));

        //test restrictions
        $restrictionsFromDb = $type2->getRestrictions();
        $restrictionsFromDb = array_combine(array_map(function (EAVTypeRelationTypeRestriction $restriction) { return $restriction->getId(); }, $restrictionsFromDb), $restrictionsFromDb);

        self::assertSameSize($type1->getRestrictions(), $restrictionsFromDb);

        /**
         * @var string                         $id
         * @var EAVTypeRelationTypeRestriction $restriction
         */
        foreach ($type1->getRestrictions() as $restriction) {
            $restrictionFromDB = $restrictionsFromDb[$restriction->getId()] ?? null;
            self::assertNotNull($restrictionFromDB);

            self::assertEquals($restriction->getId(), $restrictionFromDB->getId());
            self::assertEquals($restriction->getRestrictionTypeCode(), $restrictionFromDB->getRestrictionTypeCode());
            self::assertEquals($restriction->getRestriction(), $restrictionFromDB->getRestriction());
        }
    }


    public function testUpdateTypeWithRestrictions(): void
    {
        $type = $this->createRelationType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $typeFromDb->setTitle($typeFromDb->getTitle() . '_updated');
        $typeFromDb->setAlias($typeFromDb->getAlias() . '_updated');

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeFromDb->setMeta($basicMeta);

        foreach ($typeFromDb->getRestrictions() as $restriction) {
            $restriction->setRestrictionTypeCode($restriction->getRestrictionTypeCode() . '_updated');

            $newRestriction   = $restriction->getRestriction();
            $newRestriction[] = [
                RelationTypeRestrictions::FROM => [
                    'Person_NEW'
                ],
                RelationTypeRestrictions::TO   => [
                    'Car_NEW',
                    'Boat_NEW',
                ],
            ];

            $restriction->setRestriction($newRestriction);
            $restriction->setMeta($basicMeta);
        }

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);


    }


    public function testDeleteTypeWithRestrictions(): void
    {
        $type = $this->createRelationType();

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


    public function testAddDeleteChangeRestrictions(): void
    {
        $type = $this->createRelationType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $restrictions = $type->getRestrictions();

        // deleted
        $deletedRestriction = array_shift($restrictions);

        // new
        $newRestriction = new EAVTypeRelationTypeRestriction(Uuid::uuid4()->toString());
        $newRestriction->setRestrictionTypeCode('entity_type_restriction');
        $newRestriction->setRestriction([
            RelationTypeRestrictions::FROM => [
                'Person1'
            ],
            RelationTypeRestrictions::TO   => [
                'Car1',
                'Boat1',
            ],
        ]);
        $restrictions[] = $newRestriction;

        //updated
        /** @var EAVTypeRelationTypeRestriction $propToChange */
        $propToChange = reset($restrictions);
        $propToChange->setRestrictionTypeCode('updated');
        $propToChange->setRestriction([
            RelationTypeRestrictions::FROM => [
                'Person2'
            ],
            RelationTypeRestrictions::TO   => [
                'Car2',
                'Boat2',
            ],
        ]);

        $typeFromDb->setRestrictions($restrictions);

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                     = self::$container->get(EAVEntityManager::class);
        $this->relationTypeRepository = self::$container->get(EAVTypeRelationTypeRepository::class);
        $this->valueTypes             = self::$container->get(ValueTypes::class);
    }

}