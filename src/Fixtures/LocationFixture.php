<?php


namespace App\Fixtures;


use App\Entity\Location\Location;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixture extends AbstractFixture
{
    public static function getReferenceId()
    {
        return 'testLocation';
    }

    public function load(ObjectManager $manager)
    {
        $location1 = new Location();
        $location1->setTitle('Область 1');
        $location1->setLevel(0);
        $manager->persist($location1);

        $location2 = new Location();
        $location2->setTitle('Область 2');
        $location2->setLevel(0);
        $location2->setParent($location1);
        $manager->persist($location2);

        $manager->flush();

        $this->setReference(self::getReferenceId(), $location2);
    }
}