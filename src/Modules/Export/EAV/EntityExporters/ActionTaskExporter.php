<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\File\File;
use App\Entity\Incident\Action\ActionTask;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionTaskRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionTaskExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionTaskRepository $actionTaskRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionTaskRepository $actionTaskRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionTaskRepository = $actionTaskRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionTaskRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action_task';
    }

    protected function getFillMapping(): array
    {
        return [
            'input_data'  => function (ActionTask $actionTask) { return json_encode($actionTask->getInputData(), JSON_THROW_ON_ERROR); },
            'report_data' => function (ActionTask $actionTask) { return json_encode($actionTask->getReportData(), JSON_THROW_ON_ERROR); },
            'created_at'  => function (ActionTask $actionTask) { return $actionTask->getCreatedAt(); },
            'updated_at'  => function (ActionTask $actionTask) { return $actionTask->getUpdatedAt(); },
            'deleted'     => function (ActionTask $actionTask) { return $actionTask->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'type'       => [
                'target_eav_type_alias' => 'action_task_type',
                'getter_callback'       => function (ActionTask $actionTask) { return [ $actionTask->getType() ]; }
            ],
            'status'     => [
                'target_eav_type_alias' => 'action_task_status',
                'getter_callback'       => function (ActionTask $actionTask) { return [ $actionTask->getStatus() ]; }
            ],
            'created_by' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (ActionTask $actionTask) { return [ $actionTask->getCreatedBy() ]; }
            ],
            'updated_by' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (ActionTask $actionTask) { return [ $actionTask->getUpdatedBy() ]; }
            ],
            'action'     => [
                'target_eav_type_alias' => 'action',
                'getter_callback'       => function (ActionTask $actionTask) { return [ $actionTask->getAction() ]; }
            ],
            'has_file'   => [
                'target_eav_type_alias' => 'file',
                'getter_callback'       => function (ActionTask $actionTask) use ($em) {
                    $fileIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('files')
                        ->select('id')
                        ->where('owner_code = :owner_code')
                        ->where('owner_id = :owner_id')
                        ->setParameter('owner_code', ActionTask::getOwnerCode())
                        ->setParameter('owner_id', $actionTask->getId())
                        ->execute()
                        ->fetchAll();

                    $fileIds = array_map(function ($row) { return $row['id']; }, $fileIds);

                    return $em->getRepository(File::class)->findBy([ 'id' => $fileIds ]);
                }
            ]
        ];
    }
}
