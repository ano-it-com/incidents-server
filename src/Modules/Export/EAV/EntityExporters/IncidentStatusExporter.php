<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Domain\Incident\IncidentStatusInterface;
use App\Domain\Incident\IncidentStatusLocator;
use App\Entity\Incident\IncidentStatus;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\IncidentStatusRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class IncidentStatusExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private IncidentStatusRepository $incidentStatusRepository;

    private IncidentStatusLocator $incidentStatusLocator;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        IncidentStatusRepository $incidentStatusRepository,
        IncidentStatusLocator $incidentStatusLocator
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->incidentStatusRepository = $incidentStatusRepository;
        $this->incidentStatusLocator = $incidentStatusLocator;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->incidentStatusRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'incident_status';
    }

    protected function getFillMapping(): array
    {
        return [
            'code'       => function (IncidentStatus $incidentStatus) { return $incidentStatus->getCode(); },
            'title'      => function (IncidentStatus $incidentStatus) {
                /** @var IncidentStatusInterface $statusClass */
                $statusClass = $this->incidentStatusLocator->getClassByCode($incidentStatus->getCode());

                return $statusClass::getTitle();
            },
            'ttl'        => function (IncidentStatus $incidentStatus) {
                /** @var IncidentStatusInterface $statusClass */
                $statusClass = $this->incidentStatusLocator->getClassByCode($incidentStatus->getCode());

                return $statusClass::getTtl();
            },
            'created_at' => function (IncidentStatus $incidentStatus) { return $incidentStatus->getCreatedAt(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'incident'   => [
                'target_eav_type_alias' => 'incident',
                'getter_callback'       => function (IncidentStatus $incidentStatus) { return [ $incidentStatus->getIncident() ]; }
            ],
            'created_by' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (IncidentStatus $incidentStatus) { return [ $incidentStatus->getCreatedBy() ]; }
            ],
        ];
    }
}
