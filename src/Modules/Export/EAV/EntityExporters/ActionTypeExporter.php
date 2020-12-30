<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\IncidentType;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionTypeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionTypeExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionTypeRepository $actionTypeRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionTypeRepository $actionTypeRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionTypeRepository = $actionTypeRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionTypeRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action_type';
    }

    protected function getFillMapping(): array
    {
        return [
            'title'  => function (ActionType $actionType) { return $actionType->getTitle(); },
            'sort'   => function (ActionType $actionType) { return $actionType->getSort(); },
            'active' => function (ActionType $actionType) { return $actionType->getActive(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'belongs_to_incident_type' => [
                'target_eav_type_alias' => 'incident_type',
                'getter_callback'       => function (ActionType $actionType) use ($em) {
                    $incidentIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('incident_type_action_types')
                        ->select('incident_type_id')
                        ->where('action_type_id = :action_type_id')
                        ->setParameter('action_type_id', $actionType->getId())
                        ->execute()
                        ->fetchAll();

                    $incidentIds = array_map(function ($row) { return $row['incident_type_id']; }, $incidentIds);

                    return $em->getRepository(IncidentType::class)->findBy([ 'id' => $incidentIds ]);
                }
            ],
            'has_action_task_type'     => [
                'target_eav_type_alias' => 'action_task_type',
                'getter_callback'       => function (ActionType $actionType) { return $actionType->getActionTaskTypes()->getValues(); }
            ]
        ];
    }
}
