<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionTaskTypeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionTaskTypeExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionTaskTypeRepository $actionTaskTypeRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionTaskTypeRepository $actionTaskTypeRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionTaskTypeRepository = $actionTaskTypeRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionTaskTypeRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action_task_type';
    }

    protected function getFillMapping(): array
    {
        return [
            'title'   => function (ActionTaskType $actionTaskType) { return $actionTaskType->getTitle(); },
            'handler' => function (ActionTaskType $actionTaskType) { return $actionTaskType->getHandler(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'belongs_to_action_type' => [
                'target_eav_type_alias' => 'action_type',
                'getter_callback'       => function (ActionTaskType $actionTaskType) use ($em) {
                    $actionIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('action_type_action_task_types')
                        ->select('action_type_id')
                        ->where('action_task_type_id = :action_task_type_id')
                        ->setParameter('action_task_type_id', $actionTaskType->getId())
                        ->execute()
                        ->fetchAll();

                    $actionIds = array_map(function ($row) { return $row['action_type_id']; }, $actionIds);

                    return $em->getRepository(ActionType::class)->findBy([ 'id' => $actionIds ]);
                }
            ]
        ];
    }
}
