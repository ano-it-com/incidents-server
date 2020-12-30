<?php


namespace App\Fixtures;


use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActionTypeFixture extends AbstractFixture implements DependentFixtureInterface
{
    public static function getReferenceId()
    {
        return 'testActionType';
    }

    public function load(ObjectManager $manager)
    {
        /** @var ActionTaskType $actionTaskType */
        $actionTaskType = $this->getReference(ActionTaskTypeFixture::getReferenceId());

        $type = new ActionType();
        $type->setTitle('Тестовый тип действия');
        $type->setActive(true);
        $type->setSort(SORT_DESC);
        $type->addActionTaskType($actionTaskType);

        $manager->persist($type);
        $manager->flush();

        $this->setReference(self::getReferenceId(), $type);
    }


    public function getDependencies()
    {
        return [ActionTaskTypeFixture::class];
    }
}