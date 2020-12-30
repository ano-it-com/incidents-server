<?php

namespace App\Tests\Functional;

use App\Domain\ActionTask\Status\ActionTaskStatusDone;
use App\Domain\Action\Status\ActionStatusApproving;
use App\Domain\Action\Status\ActionStatusClarification;
use App\Domain\Action\Status\ActionStatusClosed;
use App\Domain\Action\Status\ActionStatusDraft;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Domain\Action\Status\ActionStatusNew;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Domain\Incident\Status\IncidentStatusNew;
use App\Entity\File\File;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionStatus;
use App\Entity\Incident\Action\ActionTaskStatus;
use App\Entity\Incident\Incident;
use App\Entity\Security\User;
use App\Fixtures\FileFixture;
use App\Fixtures\IncidentFixtures;
use App\Fixtures\SecurityFixtures;

class IncidentActionControllerTest extends BaseWebTestCase
{
    public function testAddActionTask()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
            FileFixture::class
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));
        /** @var Action $action */
        $action = $incident->getActions()->first();
        $oldCountTask = count($action->getActionTasks());

        $data = [
            'actionTasks' => [
                [
                    'title' => 'Тестовое задание 1',
                    'typeId' => $action->getType()->getActionTaskTypes()->first()->getId(),
                    'inputData' => [
                        ['id' => 'description', 'value' => 'описание входящих данных']
                    ],
                    'reportData' => [
                        ['id' => 'description', 'value' => 'описание данных репорта']
                    ],
                    'filesInput' => [$file->getId()],
                    'filesReport' => [$file->getId()]
                ]
            ]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/action/{$action->getId()}/task", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertCount($oldCountTask + 1, $action->getActionTasks());
    }

    public function testApprovingAction()
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
        foreach ($action->getActionTasks() as $actionTask){
            $actionTaskStatus = new ActionTaskStatus();
            $this->getEntityManager()->persist($actionTaskStatus);
            $actionTaskStatus->setActionTask($actionTask);
            $actionTaskStatus->setCreatedBy($user);
            $actionTaskStatus->setCode(ActionTaskStatusDone::CODE);
            $actionTask->setStatus($actionTaskStatus);
        }
        $action->setResponsibleUser($user);

        $this->getEntityManager()->flush();

        $data = [
            'actionIds' => [$action->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/actions/approving", $data);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals(ActionStatusApproving::CODE, $action->getStatus()->getCode());
    }

    public function testBackFromClarificationAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
            FileFixture::class
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $oldStatus = $action->getStatus();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusClarification::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);
        $action->setResponsibleUser($user);

        $this->getEntityManager()->flush();

        $data = [
            'comment' => "Неверная форма",
            'fileIds' => [$file->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/action/{$action->getId()}/back-from-clarification", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals($oldStatus->getCode(), $action->getStatus()->getCode());
    }

    public function testToClarificationAction()
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

        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
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
        $action->setResponsibleUser($user);

        $this->getEntityManager()->flush();

        $data = [
            'comment' => "Неверная форма",
            'fileIds' => [$file->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/action/{$action->getId()}/clarification", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals(ActionStatusClarification::CODE, $action->getStatus()->getCode());
    }

    public function testCloseAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusApproving::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);
        $action->setResponsibleUser($user);

        $this->getEntityManager()->flush();

        $data = [
            'actionIds' => [$action->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/actions/close", $data);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals(ActionStatusClosed::CODE, $action->getStatus()->getCode());
    }

    public function testCorrectionAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
            FileFixture::class
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $oldStatus = $action->getStatus();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusApproving::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);
        $action->setResponsibleUser($user);

        $this->getEntityManager()->flush();

        $data = [
            'comment' => "Неверная форма",
            'fileIds' => [$file->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/action/{$action->getId()}/correction", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals($oldStatus->getCode(), $action->getStatus()->getCode());
    }


    public function testTakeInWorkAction()
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

        $data = [
            'actionIds' => [$action->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/actions/take-in-work", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->assertEquals(ActionStatusInWork::CODE, $action->getStatus()->getCode());
    }

    public function testToWorkAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));

        /** @var Action $action */
        $action = $incident->getActions()->first();
        $actionStatus = new ActionStatus();
        $this->getEntityManager()->persist($actionStatus);
        $actionStatus->setCode(ActionStatusDraft::CODE);
        $actionStatus->setAction($action);
        $actionStatus->setCreatedBy($user);
        $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
        $actionStatus->setResponsibleUser($user);
        $action->setStatus($actionStatus);

        $data = [
            'actionIds' => [$action->getId()]
        ];

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/actions/work", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($action);
        $this->getEntityManager()->refresh($incident);
        $this->assertEquals(IncidentStatusInWork::CODE, $incident->getStatus()->getCode());
        $this->assertEquals(ActionStatusNew::CODE, $action->getStatus()->getCode());
    }

    public function testGetActionHistory()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        $action = $incident->getActions()->first();

        $client->amBearerAuthenticatedByLogin('supervisor');
        $client->jsonRequest('GET', "/incident/{$incident->getId()}/action/{$action->getId()}/history");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $content = $client->getJsonResponseAsArray();

        foreach ($content as $item) {
            $this->assertArrayHasKey('from', $item);
            $this->assertArrayHasKey('code', $item['from']);
            $this->assertArrayHasKey('title', $item['from']);
            $this->assertArrayHasKey('to', $item);
            $this->assertArrayHasKey('code', $item['to']);
            $this->assertArrayHasKey('title', $item['to']);
            $this->assertArrayHasKey('initiatedBy', $item);
            $this->assertArrayHasKey('initiatedAt', $item);
        }

        $this->assertNull($content[0]['from']['code']);
        $this->assertNotNull($content[1]['from']['code']);
    }
}