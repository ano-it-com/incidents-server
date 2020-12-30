<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Domain\ActionTask\ActionTaskStatusInterface;
use App\Domain\ActionTask\ActionTaskStatusLocator;
use App\Entity\Incident\Action\ActionTaskStatus;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionTaskStatusRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionTaskStatusExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionTaskStatusRepository $actionTaskStatusRepository;

    private ActionTaskStatusLocator $actionTaskStatusLocator;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionTaskStatusRepository $actionTaskStatusRepository,
        ActionTaskStatusLocator $actionTaskStatusLocator
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionTaskStatusRepository = $actionTaskStatusRepository;
        $this->actionTaskStatusLocator = $actionTaskStatusLocator;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionTaskStatusRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action_task_status';
    }

    protected function getFillMapping(): array
    {
        return [
            'code'       => function (ActionTaskStatus $incidentStatus) { return $incidentStatus->getCode(); },
            'title'      => function (ActionTaskStatus $incidentStatus) {
                /** @var ActionTaskStatusInterface $statusClass */
                $statusClass = $this->actionTaskStatusLocator->getClassByCode($incidentStatus->getCode());

                return $statusClass::getTitle();
            },
            'created_at' => function (ActionTaskStatus $incidentStatus) { return $incidentStatus->getCreatedAt(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'action_task' => [
                'target_eav_type_alias' => 'action_task',
                'getter_callback'       => function (ActionTaskStatus $actionStatus) { return [ $actionStatus->getActionTask() ]; }
            ],
            'created_by'  => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (ActionTaskStatus $actionStatus) { return [ $actionStatus->getCreatedBy() ]; }
            ],
        ];
    }
}
