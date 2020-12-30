<?php

namespace App\Fixtures;

use App\Domain\ActionTask\Status\ActionTaskStatusEmpty;
use App\Domain\Action\Status\ActionStatusDraft;
use App\Domain\Action\Status\ActionStatusNew;
use App\Domain\Incident\Status\IncidentStatusClosed;
use App\Domain\Incident\IncidentStatusInterface;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Domain\Incident\Status\IncidentStatusNew;
use App\Entity\File\File;
use App\Entity\File\FileOwnerInterface;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Action\ActionStatus;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Incident\Action\ActionTaskStatus;
use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Entity\Incident\Comment\Comment;
use App\Entity\Incident\Incident;
use App\Entity\Incident\IncidentStatus;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IncidentFixtures extends Fixture implements DependentFixtureInterface
{

    private $createdIncidents = [];

    private $createdActions = [];

    private $createdComments = [];

    private $createdActionTasks = [];

    public static function getReferenceByStatus($status)
    {
        return "incident::status::{$status}";
    }

    public function load(ObjectManager $em)
    {
        /** @var User $userCreator */
        $userCreator = $em->getRepository(User::class)->findOneBy([ 'login' => 'supervisor' ]);

        $incidentTypes = $em->getRepository(IncidentType::class)->findAll();
        /** @var IncidentType $incidentType */
        foreach ($incidentTypes as $incidentType) {
            $templates = $incidentType->getActionsTemplates();

            /** @var ActionsTemplate $template */
            foreach ($templates as $template) {
                $title       = 'Incident NEW (type: ' . $incidentType->getTitle() . ', template: ' . $template->getTitle() . ')';
                $description = 'Incident NEW Description (type: ' . $incidentType->getTitle() . ', template: ' . $template->getTitle() . ')';
                // инцидент новый
                $incident = $this->createIncident($title, $description, IncidentStatusNew::getCode(), $userCreator, $incidentType, $em);
                $this->setReference(self::getReferenceByStatus(IncidentStatusNew::getCode()), $incident);

                // действия со статусом черновик
                $this->createActionsByTemplate($incident, $template, $userCreator, $isInWork = false, $em);

                $title       = 'Incident IN WORK (type: ' . $incidentType->getTitle() . ', template: ' . $template->getTitle() . ')';
                $description = 'Incident IN WORK Description (type: ' . $incidentType->getTitle() . ', template: ' . $template->getTitle() . ')';
                // инцидент новый
                $incident = $this->createIncident($title, $description, IncidentStatusInWork::getCode(), $userCreator, $incidentType, $em);
                $this->setReference(self::getReferenceByStatus(IncidentStatusInWork::getCode()), $incident);

                // действия со статусом черновик
                $this->createActionsByTemplate($incident, $template, $userCreator, $isInWork = true, $em);
            }
        }

        $this->createFiles($em);

        $em->flush();
    }


    public function getDependencies()
    {
        return [
            SecurityFixtures::class,
            ListFixtures::class,
        ];
    }


    private function createIncident(string $title, string $description, string $statusCode, User $userCreator, IncidentType $incidentType, ObjectManager $em): Incident
    {
        // создаем инциденты
        $incident = new Incident();
        $em->persist($incident);

        $incident->setTitle($title);
        $incident->setDescription($description);
        $incident->setDate(new \DateTimeImmutable());
        $incident->setCreatedAt(new \DateTimeImmutable());
        $incident->setCreatedBy($userCreator);
        $incident->setUpdatedAt(new \DateTimeImmutable());
        $incident->setUpdatedBy($userCreator);
        $incident->setDeleted(false);

        // repeated
        if (count($this->createdIncidents)) {
            $repeatedIncidentId = array_rand($this->createdIncidents);
            $repeatedIncident   = $this->createdIncidents[$repeatedIncidentId];

            $incident->setRepeatedIncident($repeatedIncident);
        }

        $statuses = [
            IncidentStatusNew::class,
            IncidentStatusInWork::class,
            IncidentStatusClosed::class,
        ];

        /** @var  $statusCodeReference IncidentStatusInterface */
        foreach ($statuses as $statusCodeReference) {
            $incidentStatus = new IncidentStatus();
            $em->persist($incidentStatus);
            $incidentStatus->setCode($statusCodeReference::getCode());
            $incidentStatus->setIncident($incident);
            $incidentStatus->setCreatedAt(new \DateTimeImmutable());
            $incidentStatus->setCreatedBy($userCreator);

            $incident->addStatus($incidentStatus);

            if ($statusCodeReference::getCode() === $statusCode) {
                break;
            }
        }

        $count = random_int(0,3);

        for ($i = 1; $i <= $count; $i++) {
            $comment = new Comment();
            $em->persist($comment);

            $this->createdComments[$comment->getId()] = $comment;

            $comment->setText('test comment for incident ' . $incident->getId());
            $comment->setIncident($incident);
            $comment->setAction(null);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setCreatedBy($userCreator);
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $comment->setUpdatedBy($userCreator);
            $comment->setTargetGroup($userCreator->getGroups()->first());
            $comment->setDeleted(false);

            $comment->setIncident($incident);
        }

        $incident->setStatus($incidentStatus);

        $incident->setType($incidentType);
        $incident->setInfo([]);

        $this->createdIncidents[] = $incident;

        return $incident;
    }


    private function createActionsByTemplate(Incident $incident, ActionsTemplate $template, User $userCreator, bool $isInWork, ObjectManager $em): void
    {
        $actionMappingToCreate = $template->getActionsMapping();

        foreach ($actionMappingToCreate as $mapping) {
            $code    = $mapping['actionTypeId'];
            $groupId = $mapping['groupId'];

            /** @var ActionType $actionType */
            $actionType = $em->getRepository(ActionType::class)->find($code);

            /** @var Group $group */
            if ($groupId) {
                $group = $em->getRepository(Group::class)->find($groupId);
            } else {
                $group = $em->getRepository(Group::class)->findBy([], [ 'id' => 'desc' ], 1)[0];
            }

            $action = new Action();
            $em->persist($action);

            $this->createdActions[$action->getId()] = $action;

            $action->setType($actionType);
            $action->setCreatedAt(new \DateTimeImmutable());
            $action->setCreatedBy($userCreator);
            $action->setUpdatedAt(new \DateTimeImmutable());
            $action->setUpdatedBy($userCreator);
            $action->setResponsibleUser(null);
            $action->setResponsibleGroup($group);
            $action->setDeleted(false);

            $count = random_int(0,3);

            for ($i = 1; $i <= $count; $i++) {
                $comment = new Comment();
                $em->persist($comment);

                $this->createdComments[$comment->getId()] = $comment;

                $comment->setText('test comment for action ' . $incident->getId() . ' - ' . $action->getId());
                $comment->setIncident($incident);
                $comment->setAction($action);
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setCreatedBy($userCreator);
                $comment->setUpdatedAt(new \DateTimeImmutable());
                $comment->setUpdatedBy($userCreator);
                $comment->setTargetGroup($userCreator->getGroups()->first());
                $comment->setDeleted(false);

                $comment->setIncident($incident);
                $comment->setAction($action);
            }

            $actionStatus = new ActionStatus();
            $em->persist($actionStatus);
            $actionStatus->setCreatedAt(new \DateTimeImmutable());
            $actionStatus->setCreatedBy($userCreator);
            $actionStatus->setAction($action);
            $statusCode = ActionStatusDraft::getCode();
            $actionStatus->setCode($statusCode);
            $actionStatus->setResponsibleUser(null);
            $actionStatus->setResponsibleGroup($group);

            $action->addStatus($actionStatus);

            if ($isInWork) {
                $actionStatus = new ActionStatus();
                $em->persist($actionStatus);
                $actionStatus->setCreatedAt(new \DateTimeImmutable());
                $actionStatus->setCreatedBy($userCreator);
                $actionStatus->setAction($action);
                $statusCode = ActionStatusNew::getCode();
                $actionStatus->setCode($statusCode);
                $actionStatus->setResponsibleUser(null);
                $actionStatus->setResponsibleGroup($group);
            }

            $action->setStatus($actionStatus);
            $action->setIncident($incident);
            $action->setTemplateId($template->getId());

            $actionTaskTypes = $actionType->getActionTaskTypes();
            /** @var ActionTaskType $actionTaskType */
            foreach ($actionTaskTypes as $actionTaskType) {
                $actionTask = new ActionTask();
                $em->persist($actionTask);

                $this->createdActionTasks[$actionTask->getId()] = $actionTask;

                $actionTask->setAction($action);
                $actionTask->setCreatedAt(new \DateTimeImmutable());
                $actionTask->setCreatedBy($userCreator);
                $actionTask->setUpdatedAt(new \DateTimeImmutable());
                $actionTask->setUpdatedBy($userCreator);
                $actionTask->setDeleted(false);

                $actionTaskStatus = new ActionTaskStatus();
                $em->persist($actionTaskStatus);
                $actionTaskStatus->setCreatedAt(new \DateTimeImmutable());
                $actionTaskStatus->setCreatedBy($userCreator);
                $actionTaskStatus->setActionTask($actionTask);
                $actionTaskStatus->setCode(ActionTaskStatusEmpty::getCode());

                $actionTask->setStatus($actionTaskStatus);
                $actionTask->setType($actionTaskType);
            }


        }
    }


    private function createFiles(ObjectManager $em): void
    {
        /** @var User $userCreator */
        $userCreator = $em->getRepository(User::class)->findOneBy([ 'login' => 'supervisor' ]);

        $mapping = [
            'createdIncidents'   => Incident::getOwnerCode(),
            'createdActions'     => Action::getOwnerCode(),
            'createdComments'    => Comment::getOwnerCode(),
            'createdActionTasks' => ActionTask::getOwnerCode(),
        ];

        foreach ($mapping as $prop => $ownerCode) {
            /** @var FileOwnerInterface $entity */
            foreach ($this->{$prop} as $entity) {
                $count = random_int(0,3);

                for ($i = 1; $i <= $count; $i++) {
                    $file = new File();
                    $em->persist($file);
                    $file->setOwnerCode($ownerCode);
                    $file->setOwnerId($entity->getId());
                    $file->setPath('/test');
                    $file->setOriginalName('test-file.jpg');
                    $file->setSize(777);
                    $file->setDeleted(false);
                    $file->setCreatedAt(new \DateTimeImmutable());
                    $file->setCreatedBy($userCreator);
                }
            }
        }

        $em->flush();
    }
}
