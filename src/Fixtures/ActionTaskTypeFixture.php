<?php


namespace App\Fixtures;


use App\Entity\File\File;
use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Security\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActionTaskTypeFixture extends AbstractFixture
{
    public static function getReferenceId()
    {
        return 'testActionTaskType';
    }

    public function load(ObjectManager $manager)
    {
        $type = new ActionTaskType();
        $type->setTitle('Тестовый тип задания');
        $type->setHandler('handler');

        $manager->persist($type);
        $manager->flush();

        $this->setReference(self::getReferenceId(), $type);
    }
}