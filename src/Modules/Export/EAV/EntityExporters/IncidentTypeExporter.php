<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Incident\IncidentType;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\IncidentTypeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class IncidentTypeExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private IncidentTypeRepository $incidentTypeRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        IncidentTypeRepository $incidentTypeRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->incidentTypeRepository = $incidentTypeRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->incidentTypeRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'incident_type';
    }

    protected function getFillMapping(): array
    {
        return [
            'handler'     => function (IncidentType $incidentType) { return $incidentType->getHandler(); },
            'title'       => function (IncidentType $incidentType) { return $incidentType->getTitle(); },
            'description' => function (IncidentType $incidentType) { return $incidentType->getDescription(); },
            'deleted'     => function (IncidentType $incidentType) { return $incidentType->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'has_action_type' => [
                'target_eav_type_alias' => 'action_type',
                'getter_callback'       => function (IncidentType $incidentType) { return $incidentType->getActionTypes()->getValues(); }
            ]
        ];
    }
}
