<?php

namespace App\Tests\Functional;

use App\Domain\Action\Status\ActionStatusClosed;
use App\Domain\Incident\Status\IncidentStatusClosed;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Domain\Incident\Status\IncidentStatusNew;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionStatus;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Incident\Incident;
use App\Entity\Location\Location;
use App\Fixtures\IncidentFixtures;
use App\Fixtures\ListFixtures;
use App\Fixtures\LocationFixture;
use App\Fixtures\SecurityFixtures;
use App\Entity\File\File;
use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Fixtures\ActionTaskTypeFixture;
use App\Fixtures\ActionTemplateFixture;
use App\Fixtures\ActionTypeFixture;
use App\Fixtures\FileFixture;
use App\Fixtures\IncidentTypeFixture;

class IncidentControllerTest extends BaseWebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentTypeFixture::class,
            FileFixture::class,
            ActionTaskTypeFixture::class,
            ActionTypeFixture::class,
            ActionTemplateFixture::class,
            LocationFixture::class
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var IncidentType $type */
        $type = $fixtures->getReferenceRepository()->getReference(IncidentTypeFixture::getReferenceId());
        /** @var Group $responsibleGroup */
        $responsibleGroup = $fixtures->getReferenceRepository()->getReference('group::executor');
        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
        /** @var ActionType $actionType */
        $actionType = $fixtures->getReferenceRepository()->getReference(ActionTypeFixture::getReferenceId());
        /** @var ActionTaskType $actionTaskType */
        $actionTaskType = $fixtures->getReferenceRepository()->getReference(ActionTaskTypeFixture::getReferenceId());
        /** @var ActionsTemplate $actionTemplate */
        $actionTemplate = $fixtures->getReferenceRepository()->getReference(ActionTemplateFixture::getReferenceId());
        /** @var Location $location */
        $location = $fixtures->getReferenceRepository()->getReference(LocationFixture::getReferenceId());

        $data = [
            'title' => 'Тестовый инцидент',
            'description' => 'Описание',
            'info' => [
                ['id' => 'locationId', 'value' => $location->getId()],
                ['id' => 'priority', 'value' => 25],
                ['id' => 'coverage', 'value' => 500]
            ],
            'typeId' => $type->getId(),
            'responsibleGroups' => [$responsibleGroup->getId()],
            'repeatedIncidentId' => null,
            'files' => [$file->getId()],
            'templates' => [
                [
                    'templateId' => $actionTemplate->getId(),
                    'actions' => $actionTemplate->getActionsMapping()
                ]
            ],
            'actions' => [
                [
                    'responsibleGroup' => [$responsibleGroup->getId()],
                    'typeId' => $actionType->getId(),
                    'tasks' => [
                        [
                            'title' => 'Тестовое задание 1',
                            'typeId' => $actionTaskType->getId(),
                            'inputData' => ['someField' => 'sameValue'],
                            'reportData' => ['someField' => 'sameValue'],
                            'filesInput' => [$file->getId()],
                            'filesReport' => [$file->getId()]
                        ]
                    ],
                    'files' => [$file->getId()]
                ]
            ]
        ];

        $client->jsonRequest('POST', '/incident', $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());
    }

    public function testUpdate()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            IncidentFixtures::class,
            SecurityFixtures::class,
            LocationFixture::class
        ]);
        $client->amBearerAuthenticatedByLogin('supervisor');

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));
        /** @var Incident $incident2 */
        $incident2 = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));
        /** @var Group $responsibleGroup */
        $responsibleGroup = $fixtures->getReferenceRepository()->getReference('group::executor');
        /** @var Location $location */
        $location = $fixtures->getReferenceRepository()->getReference(LocationFixture::getReferenceId());

        $data = [
            'title' => 'Обновленный тестовый инцидент',
            'description' => 'Новое описание',
            'info' => [
                ['id' => 'locationId', 'value' => $location->getId()],
                ['id' => 'priority', 'value' => 25],
                ['id' => 'coverage', 'value' => 500]
            ],
            'typeId' => $incident->getType()->getId(),
            'responsibleGroups' => [$responsibleGroup->getId()],
            'repeatedIncidentId' => $incident2->getId(),
            'actions' => []
        ];

        $client->jsonRequest('PUT', "/incident/{$incident->getId()}", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testClose()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            IncidentFixtures::class,
            SecurityFixtures::class,
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));
        foreach ($incident->getActions() as $action) {
            $actionStatus = new ActionStatus();
            $this->getEntityManager()->persist($actionStatus);
            $actionStatus->setCode(ActionStatusClosed::CODE);
            $actionStatus->setAction($action);
            $actionStatus->setCreatedBy($user);
            $actionStatus->setResponsibleGroup($action->getStatus()->getResponsibleGroup());
            $actionStatus->setResponsibleUser($user);
            $action->setStatus($actionStatus);
        }
        $this->getEntityManager()->flush();

        $client->jsonRequest('POST', "/incident/{$incident->getId()}/close");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $this->getEntityManager()->refresh($incident);
        $this->assertEquals(IncidentStatusClosed::CODE, $incident->getStatus()->getCode());
    }

    public function testAddAction()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
            FileFixture::class,
        ]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        /** @var Group $responsibleGroup */
        $responsibleGroup = $fixtures->getReferenceRepository()->getReference('group::executor');
        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceDeletedUserFile($user->getLogin()));
        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));
        /** @var Action $action */
        $action = $incident->getActions()->first();
        /** @var ActionTask $task */
        $task = $action->getActionTasks()->first();

        $oldActionCount = count($incident->getActions());
        $data = [
            'actions' => [
                [
                    'responsibleGroup' => [$responsibleGroup->getId()],
                    'typeId' => $action->getType()->getId(),
                    'tasks' => [
                        [
                            'title' => 'Тестовое задание 1',
                            'typeId' => $task->getType()->getId(),
                            'inputData' => ['someField' => 'sameValue'],
                            'reportData' => ['someField' => 'sameValue'],
                            'filesInput' => [$file->getId()],
                            'filesReport' => [$file->getId()]
                        ],
                    ],
                    'files' => [$file->getId()]
                ]
            ]
        ];

        $client->jsonRequest('PUT', "/incident/{$incident->getId()}/actions", $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson(200, $client->getResponse()->getContent());

        $this->getEntityManager()->refresh($incident);
        $this->assertCount($oldActionCount + 1, $incident->getActions());
    }

    public function testGetList()
    {
        $client = static::createClient();
        $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);
        $client->amBearerAuthenticatedByLogin('supervisor');
        $client->request('GET', "/incident");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGet()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);
        $client->amBearerAuthenticatedByLogin('supervisor');

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusNew::CODE));

        $client->request('GET', "/incident/{$incident->getId()}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $response = $client->getJsonResponseAsArray();
        self::assertEquals($response['id'], $incident->getId());
    }

    public function testGetOptions()
    {
        $client = static::createClient();
        $this->loadFixtures([
            SecurityFixtures::class,
            ListFixtures::class,
        ]);
        $client->amBearerAuthenticatedByLogin('supervisor');

        $client->request('GET', "/incident/options");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $response = $client->getJsonResponseAsArray();

        foreach (['groups', 'types', 'handlers', 'actionTaskHandlers', 'templates', 'filters', 'statuses'] as $field) {
            $this->assertArrayHasKey($field, $response);
            $this->assertNotEmpty($response[$field]);
        }

        foreach (['incident', 'action', 'task'] as $field) {
            $this->assertArrayHasKey($field, $response['statuses']);
            $this->assertNotEmpty( $response['statuses'][$field]);
        }

        foreach (['status_code', 'created_by_id'] as $field){
            $this->assertArrayHasKey($field, $response['filters']);
        }
    }

    public function testGetIncidentHistory()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([
            SecurityFixtures::class,
            IncidentFixtures::class,
        ]);

        /** @var Incident $incident */
        $incident = $fixtures->getReferenceRepository()->getReference(IncidentFixtures::getReferenceByStatus(IncidentStatusInWork::CODE));

        $client->amBearerAuthenticatedByLogin('supervisor');
        $client->jsonRequest('GET', "/incident/{$incident->getId()}/history");

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
