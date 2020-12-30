<?php


namespace App\Tests\Functional;


use App\Domain\ActionTask\Status\ActionTaskStatusDone;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Entity\File\File;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionStatus;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Incident\Incident;
use App\Entity\Security\User;
use App\Fixtures\FileFixture;
use App\Fixtures\IncidentFixtures;
use App\Fixtures\SecurityFixtures;

class IncidentActionTaskControllerTest extends BaseWebTestCase
{
    public function testSetStatusAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::executor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusInWork::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);
        $task = $action->getActionTasks()->first();
        $statusCode = ActionTaskStatusDone::CODE;
        $this->getEntityManager()->flush();


        $client->jsonRequest('POST', "/incident/{$incident->getId()}/action/{$action->getId()}/task/{$task->getId()}/status/{$statusCode}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($task);
        $this->assertEquals($statusCode, $task->getStatus()->getCode());
    }

    public function testUpdateReportAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
            FileFixture::class
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::executor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));
        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusInWork::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);
        /** @var ActionTask $task */
        $task = $action->getActionTasks()->first();
        $this->getEntityManager()->flush();

        $data = [
            'reportData' => ['message' => 'Фейк'],
            'filesReport' => [$file->getId()]
        ];

        $client->jsonRequest('PUT', "/incident/{$incident->getId()}/action/{$action->getId()}/task/{$task->getId()}/report", $data);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($task);
        $this->getEntityManager()->refresh($file);
        $this->assertEquals($data['reportData'], $task->getReportData());
        $this->assertEquals($task->getId(), $file->getOwnerId());
    }
}