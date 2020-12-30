<?php

namespace App\Fixtures;

use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Entity\Incident\IncidentType;
use App\Entity\Security\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ListFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $types   = $this->getIncidentTypes();
        $actions = $this->getActionsMapping();
        $groups  = [];

        /** @var Group $group */
        foreach ($manager->getRepository(Group::class)->findAll() as $group) {
            $groups[$group->getCode()] = $group;
        }

        $actionTaskTypesByTitle = [];

        foreach ($types as $type) {
            $incidentType = new IncidentType();
            $incidentType->setTitle($type['title']);
            $incidentType->setDescription($type['description']);
            $incidentType->setHandler($type['handler']);
            $incidentType->setDeleted(false);

            $manager->persist($incidentType);

            $actionsMapping = $actions[$incidentType->getHandler()] ?? [];

            $actionSort = 0;

            $i = 0;
            foreach ($actionsMapping as $action) {
                $i++;
                $template = [
                    'title'   => $action['title'],
                    'actions' => [],
                    'sort'    => $i * 10
                ];

                $actionInTemplateSort = 0;
                foreach ($action['actions'] as $actionTitle => $actionInfo) {
                    $actionSort++;
                    $actionInTemplateSort++;

                    $actionType = new ActionType();
                    $actionType->setTitle($actionTitle);
                    $actionType->setActive(true);
                    $actionType->setSort($actionSort * 10);

                    $manager->persist($actionType);

                    $incidentType->addActionType($actionType);

                    $groupId = (isset($actionInfo['arm'])) ? $groups['executor' . $actionInfo['arm']]->getId() : null;

                    $template['actions'][] = [
                        'actionTypeId' => $actionType->getId(),
                        'sort'         => $actionInTemplateSort,
                        'groupId'      => $groupId,
                    ];

                    // таски
                    $actionTaskTypeTitle = $actionInfo['task'];

                    $actionTaskType = $actionTaskTypesByTitle[$actionTaskTypeTitle] ?? null;
                    if ( ! $actionTaskType) {
                        $actionTaskType = new ActionTaskType();
                        $actionTaskType->setTitle($actionTaskTypeTitle);
                        $actionTaskType->setHandler('detail_report');
                        $actionTaskTypesByTitle[$actionTaskTypeTitle] = $actionTaskType;

                        $manager->persist($actionTaskType);
                    }

                    $actionType->addActionTaskType($actionTaskType);
                }

                $templateEntity = new ActionsTemplate();
                $templateEntity->setDeleted(false);
                $templateEntity->setTitle($template['title']);
                $templateEntity->setActionsMapping($template['actions']);
                $templateEntity->setSort($template['sort']);
                $templateEntity->setIncidentType($incidentType);
                $manager->persist($templateEntity);
            }
        }

        $manager->flush();
    }


    private function getIncidentTypes(): array
    {
        return [
            [
                'title'       => 'Инцидент',
                'description' => 'Описание типа инцидента',
                'handler'     => 'incident',
            ],
            [
                'title'       => 'Инцидент ИБ',
                'description' => 'Описание типа инцидента ИБ',
                'handler'     => 'security',
            ],
        ];
    }


    private function getActionsMapping(): array
    {
        return [
            'security' => [
                [
                    'title'   => 'Проведение внутреннего аудита',
                    'actions' => [
                        'Отключить оборудование от сегмента сети' => [ 'task' => 'Отключить оборудование от сегмента сети организации' ],
                        'Предупредить отвественных лиц'               => [ 'task' => 'Предупредить отвественных лиц' ],
                        'Собрать файлы журналов с зараженной машины'  => [ 'task' => 'Собрать файлы журналов' ],
                    ],
                ],
            ],
            'incident' => [
                [
                    'title'   => 'Оповещение руководства',
                    'actions' => [
                        'Оповестить Директора'                                            => [ 'arm' => 1, 'task' => 'Оповестить' ],
                        'Оповестить заместителя Директора, ответственного за направление' => [ 'arm' => 2, 'task' => 'Оповестить' ],
                        'Оповестить непосредственного руководителя'                       => [ 'arm' => 1, 'task' => 'Оповестить' ],
                        'Оповестить дежурного'                                            => [ 'arm' => 3, 'task' => 'Оповестить' ],
                        'Оповестить сотрудников отдела'                                   => [ 'arm' => 1, 'task' => 'Оповестить' ],
                    ],
                ],

                [
                    'title'   => 'Оповещение надзорного органа',
                    'actions' => [
                        'Подготовить текст SMS-сообщения для оповещения'                    => [ 'arm' => 3, 'task' => 'Подготовить текст SMS-сообщения для оповещения' ],
                        'Оповестить по телефону руководителя надзорного органа'             => [ 'arm' => 1, 'task' => 'Оповестить по телефону' ],
                        'Оповестить по телефону заместителя руководителя надзорного органа' => [ 'arm' => 1, 'task' => 'Оповестить по телефону' ],
                        'Оповестить ответственных сотрудников надзорного органа'            => [ 'arm' => 3, 'task' => 'Оповестить путем рассылки SMS-сообщений' ],
                    ],
                ],

                [
                    'title'   => 'Информирование населения',
                    'actions' => [
                        'Подготовить предложения по возможным способам информирования населения' => [
                            'arm'  => 4,
                            'task' => 'Подготовить предложения'
                        ],
                        'Информировать граждан о проишедшем инциденте'                           => [
                            'arm'  => 4,
                            'task' => 'Информирование населения'
                        ],
                        'Контроль информирования населения'                                      => [
                            'arm'  => 4,
                            'task' => 'Контроль и доклад'
                        ],

                    ],
                ],
                [
                    'title'   => 'Ликвидация последствий',
                    'actions' => [
                        'Составить план ликвидации'            => [ 'arm' => 1, 'task' => 'Составление плана ликвидации' ],
                        'Согласовать с ответственными лицами'  => [ 'arm' => 2, 'task' => 'Согласование с ответственными лицами' ],
                        'Организовать подготовительные работы' => [ 'arm' => 1, 'task' => 'Организация подготовительных работ' ],
                        'Ликвидировать последствия'            => [ 'arm' => 3, 'task' => 'Ликвидация последствий' ],
                        'Контролировать результаты ликвидации' => [ 'arm' => 1, 'task' => 'Контроль и доклад' ],
                    ],
                ],
                [
                    'title'   => 'Подготовка отчетов по итогам инцидента',
                    'actions' => [
                        'Подготовить отчет по ликвидации инцидента' => [ 'arm' => 1, 'task' => 'Подготовка отчета' ],
                        'Согласовать отчет'                         => [ 'arm' => 2, 'task' => 'Согласование с ответственными лицами' ],
                    ],
                ],

            ],
        ];
    }


    public function getDependencies()
    {
        return [
            SecurityFixtures::class,
        ];
    }
}
