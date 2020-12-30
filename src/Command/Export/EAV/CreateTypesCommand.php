<?php

namespace App\Command\Export\EAV;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BoolType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\IntType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateTypesCommand extends Command
{

    protected static $defaultName = 'export:eav:types_create';

    private EAVEntityManagerInterface $eavEm;

    private EAVTypeRepositoryInterface $typeRepository;

    /**
     * @var EAVEntityRelationTypeRepository
     */
    private EAVEntityRelationTypeRepository $entityRelationTypeRepository;


    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository
    ) {
        parent::__construct("Create Types for export");
        $this->eavEm                        = $eavEm;
        $this->typeRepository               = $typeRepository;
        $this->entityRelationTypeRepository = $entityRelationTypeRepository;
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->createTypes($io);
        $this->createEntityRelationTypes($io);

        $io->success('End');

        return 0;
    }


    private function getTypes(): array
    {
        $incidentType = [
            'alias'       => 'incident_type',
            'title'       => 'Тип инцидента',
            '_properties' => [
                [
                    'alias'      => 'handler',
                    'title'      => 'Тип обработчика',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'description',
                    'title'      => 'Описание',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $actionType = [
            'alias'       => 'action_type',
            'title'       => 'Тип действия',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'sort',
                    'title'      => 'Сортировка',
                    'value_type' => IntType::class
                ],
                [
                    'alias'      => 'active',
                    'title'      => 'Флаг активности',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $actionTaskType = [
            'alias'       => 'action_task_type',
            'title'       => 'Тип рекомендации',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'handler',
                    'title'      => 'Тип обработчика',
                    'value_type' => TextType::class
                ],
            ]
        ];

        $user = [
            'alias'       => 'user',
            'title'       => 'Пользователь',
            '_properties' => [
                [
                    'alias'      => 'login',
                    'title'      => 'Логин',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'email',
                    'title'      => 'Почта',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'first_name',
                    'title'      => 'Имя',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'last_name',
                    'title'      => 'Фамилия',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'telegram_id',
                    'title'      => 'Идентификатор Телеграм',
                    'value_type' => TextType::class
                ],
            ]
        ];

        $group = [
            'alias'       => 'group',
            'title'       => 'Группа пользователя',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'code',
                    'title'      => 'Код',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'public',
                    'title'      => 'Флаг публичности группы',
                    'value_type' => BoolType::class
                ],
            ]
        ];

        $location = [
            'alias'       => 'location',
            'title'       => 'Местоположение',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'level',
                    'title'      => 'Уровень вложенности',
                    'value_type' => IntType::class
                ],
            ]
        ];

        $genericIncident = [
            'alias'       => 'incident',
            'title'       => 'Инцидент',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'title',
                    'title'      => 'Описание',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'info',
                    'title'      => 'Дполонительная информация',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'date',
                    'title'      => 'Дата',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'updated_at',
                    'title'      => 'Дата и время изменения',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $genericAction = [
            'alias'       => 'action',
            'title'       => 'Действие',
            '_properties' => [
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'updated_at',
                    'title'      => 'Дата и время изменения',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $genericActionTask = [
            'alias'       => 'action_task',
            'title'       => 'Рекомендация',
            '_properties' => [
                [
                    'alias'      => 'input_data',
                    'title'      => 'Постановка задачи',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'report_data',
                    'title'      => 'Отчет по задаче',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'updated_at',
                    'title'      => 'Дата и время изменения',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $comment = [
            'alias'       => 'comment',
            'title'       => 'Комментарий',
            '_properties' => [
                [
                    'alias'      => 'text',
                    'title'      => 'Текст комментария',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'updated_at',
                    'title'      => 'Дата и время изменения',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $file = [
            'alias'       => 'file',
            'title'       => 'Файл',
            '_properties' => [
                [
                    'alias'      => 'path',
                    'title'      => 'Путь к файлу',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'original_name',
                    'title'      => 'Оригинальное название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'size',
                    'title'      => 'размер',
                    'value_type' => IntType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
                [
                    'alias'      => 'deleted',
                    'title'      => 'Флаг удаления',
                    'value_type' => BoolType::class
                ]
            ]
        ];

        $incidentStatus = [
            'alias'       => 'incident_status',
            'title'       => 'Статус инцидента',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'code',
                    'title'      => 'Код статуса',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'ttl',
                    'title'      => 'SLA',
                    'value_type' => IntType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
            ]
        ];

        $actionStatus = [
            'alias'       => 'action_status',
            'title'       => 'Статус действия',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'code',
                    'title'      => 'Код статуса',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'ttl',
                    'title'      => 'SLA',
                    'value_type' => IntType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
            ]
        ];

        $actionTaskStatus = [
            'alias'       => 'action_task_status',
            'title'       => 'Статус рекомендации',
            '_properties' => [
                [
                    'alias'      => 'title',
                    'title'      => 'Название',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'code',
                    'title'      => 'Код статуса',
                    'value_type' => TextType::class
                ],
                [
                    'alias'      => 'created_at',
                    'title'      => 'Дата и время создания',
                    'value_type' => DateTimeType::class
                ],
            ]
        ];

        return [
            $incidentType,
            $actionType,
            $actionTaskType,
            $user,
            $group,
            $location,
            $genericIncident,
            $genericAction,
            $genericActionTask,
            $comment,
            $file,
            $incidentStatus,
            $actionStatus,
            $actionTaskStatus
        ];
    }


    private function createTypes(SymfonyStyle $io)
    {
        $existedTypes = $this->typeRepository->findBy([], [], 1);
        if (count($existedTypes) > 0) {
            throw new \RuntimeException('You already have types in DB.');
        }

        $types = $this->getTypes();

        foreach ($types as $type) {
            $eavTypeId = Uuid::uuid4();
            $eavType   = new EAVType($eavTypeId);
            $eavType->setTitle($type['title']);
            $eavType->setAlias($type['alias']);

            $io->writeln('Creating ' . $type['title'] . ' type...');

            $properties = [];
            foreach ($type['_properties'] as $property) {
                $eavPropertyId  = Uuid::uuid4();
                $valueTypeClass = $property['value_type'];
                $eavProperty    = new EAVTypeProperty($eavPropertyId, $eavType, new $valueTypeClass);
                $eavProperty->setTitle($property['title']);
                $eavProperty->setAlias($property['alias']);

                $properties[] = $eavProperty;
            }

            $eavType->setProperties($properties);
            $this->eavEm->persist($eavType);
        }

        $this->eavEm->flush();
    }


    private function createEntityRelationTypes(SymfonyStyle $io)
    {
        $existedTypes = $this->entityRelationTypeRepository->findBy([], [], 1);
        if (count($existedTypes) > 0) {
            throw new \RuntimeException('You already have entity relation types in DB.');
        }

        $addedAliases = [];

        foreach ($this->getEntityRelationTypes() as $types) {
            foreach ($types as $type) {
                if (in_array($type['alias'], $addedAliases, true)) {
                    continue;
                }

                $io->writeln('Creating ' . $type['title'] . ' entity relation type...');

                $relationType = new EAVEntityRelationType(Uuid::uuid4());
                $relationType->setAlias($type['alias']);
                $relationType->setTitle($type['title']);

                $this->eavEm->persist($relationType);

                $addedAliases[] = $type['alias'];
            }
        }

        $this->eavEm->flush();
    }


    private function getEntityRelationTypes(): array
    {
        //Creating Тип инцидента type...
        $incidentType = [
            [ 'alias' => 'has_action_type', 'title' => 'Имеет тип действия', ],
        ];

        //Creating Тип действия type...
        $actionType = [
            [ 'alias' => 'belongs_to_incident_type', 'title' => 'Принадлежит к типу инцидента', ],
            [ 'alias' => 'has_action_task_type', 'title' => 'Имеет тип рекомендации', ],
        ];
        //Creating Тип рекомендации type...
        $actionTaskType = [
            [ 'alias' => 'belongs_to_action_type', 'title' => 'Принадлежит к типу действия', ],
        ];
        //Creating Пользователь type...
        $user = [
            [ 'alias' => 'belongs_to_group', 'title' => 'Принадлежит к группе', ],
        ];
        //Creating Группа пользователя type...
        $group = [
            [ 'alias' => 'has_user', 'title' => 'Имеет пользователя', ],
        ];
        //Creating Инцидент type...
        $incident = [
            [ 'alias' => 'type', 'title' => 'Имеет тип', ],
            [ 'alias' => 'status', 'title' => 'Текущий статус', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
            [ 'alias' => 'updated_by', 'title' => 'Изменен пользователем', ],
            [ 'alias' => 'has_status', 'title' => 'Имеет статус', ],
            [ 'alias' => 'action', 'title' => 'Имеет действие', ],
            [ 'alias' => 'responsible_group', 'title' => 'Ответственная группа', ],
            [ 'alias' => 'has_file', 'title' => 'Имеет файл', ],
        ];
        //Creating Действие type...
        $action = [
            [ 'alias' => 'incident', 'title' => 'Принадлежит инциденту', ],
            [ 'alias' => 'type', 'title' => 'Имеет тип', ],
            [ 'alias' => 'status', 'title' => 'Текущий статус', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
            [ 'alias' => 'updated_by', 'title' => 'Изменен пользователем', ],
            [ 'alias' => 'has_status', 'title' => 'Имеет статус', ],
            [ 'alias' => 'action_task', 'title' => 'Имеет рекомендацию', ],
            [ 'alias' => 'responsible_group', 'title' => 'Ответственная группа', ],
            [ 'alias' => 'responsible_user', 'title' => 'Ответственный пользователь', ],
            [ 'alias' => 'comment', 'title' => 'Комментарий', ],
            [ 'alias' => 'has_file', 'title' => 'Имеет файл', ],
        ];
        //Creating Рекомендация type...
        $actionTask = [
            [ 'alias' => 'action', 'title' => 'Принадлежит действию', ],
            [ 'alias' => 'type', 'title' => 'Имеет тип', ],
            [ 'alias' => 'status', 'title' => 'Текущий статус', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
            [ 'alias' => 'updated_by', 'title' => 'Изменен пользователем', ],
            [ 'alias' => 'has_status', 'title' => 'Имеет статус', ],
            [ 'alias' => 'has_file', 'title' => 'Имеет файл', ],
        ];
        //Creating Комментарий type...
        $comment = [
            [ 'alias' => 'action', 'title' => 'Действие', ],
            [ 'alias' => 'incident', 'title' => 'Инцидент', ],
            [ 'alias' => 'type', 'title' => 'Имеет тип', ],
            [ 'alias' => 'status', 'title' => 'Текущий статус', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
            [ 'alias' => 'updated_by', 'title' => 'Изменен пользователем', ],
            [ 'alias' => 'target_group', 'title' => 'Целевая группа', ],
            [ 'alias' => 'has_file', 'title' => 'Имеет файл', ],
        ];
        //Creating Файл type...
        $file = [
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
        ];
        //Creating Статус инцидента type...
        $incidentStatus = [
            [ 'alias' => 'incident', 'title' => 'Инцидент', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
        ];
        //Creating Статус действия type...
        $actionStatus = [
            [ 'alias' => 'action', 'title' => 'Действие', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
        ];
        //Creating Статус рекомендации type...
        $actionTaskStatus = [
            [ 'alias' => 'action_task', 'title' => 'Рекомендация', ],
            [ 'alias' => 'created_by', 'title' => 'Создан пользователем', ],
        ];

        return [
            $incidentType,
            $actionType,
            $actionTaskType,
            $user,
            $group,
            $incident,
            $action,
            $actionTask,
            $comment,
            $file,
            $incidentStatus,
            $actionStatus,
            $actionTaskStatus,
        ];

    }

}