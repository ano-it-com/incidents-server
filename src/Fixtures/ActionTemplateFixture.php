<?php


namespace App\Fixtures;


use App\Fixtures\SecurityFixtures;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActionTemplateFixture extends AbstractFixture implements DependentFixtureInterface
{

    public static function getReferenceId()
    {
        return 'testActionTemplate';
    }

    public function load(ObjectManager $manager)
    {
        /** @var IncidentType $type */
        $type = $this->getReference(IncidentTypeFixture::getReferenceId());
        /** @var ActionType $actionType */
        $actionType = $this->getReference(ActionTypeFixture::getReferenceId());
        /** @var Group $groups */
        $group = $this->getReference('group::supervisor');

        $tmpl = new ActionsTemplate();
        $tmpl->setTitle('Тестовый инцидент');
        $tmpl->setIncidentType($type);
        $tmpl->setSort(10);
        $tmpl->setDeleted(false);
        $tmpl->setActionsMapping([
            [
                'actionTypeId' => $actionType->getId(),
                'sort' => 1,
                'groupId' => $group->getId(),
            ]
        ]);

        $manager->persist($tmpl);
        $manager->flush();
        $this->setReference($this->getReferenceId(), $tmpl);
    }


    public function getDependencies()
    {
        return [IncidentTypeFixture::class, SecurityFixtures::class, ActionTypeFixture::class];
    }
}