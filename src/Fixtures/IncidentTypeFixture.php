<?php


namespace App\Fixtures;


use App\Domain\Incident\TypeHandler\IncidentTypeHandler;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\IncidentType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IncidentTypeFixture extends AbstractFixture implements DependentFixtureInterface
{

    public static function getReferenceId()
    {
        return 'testIncidentType';
    }

    public function load(ObjectManager $manager)
    {
        /** @var ActionType $actionTaskType */
        $actionType = $this->getReference(ActionTypeFixture::getReferenceId());

        $type = new IncidentType();
        $type->setDescription('Описание');
        $type->setDeleted(false);
        $type->setTitle('Новый тип');
        $type->setHandler(IncidentTypeHandler::getCode());
        $type->addActionType($actionType);

        $manager->persist($type);
        $manager->flush();

        $this->setReference($this->getReferenceId(), $type);
    }

    public function getDependencies()
    {
        return [ActionTypeFixture::class];
    }
}