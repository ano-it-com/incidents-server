<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\File\File;
use App\Entity\Incident\Incident;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\IncidentRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class IncidentExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private IncidentRepository $incidentRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        IncidentRepository $incidentRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->incidentRepository = $incidentRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->incidentRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'incident';
    }

    protected function getFillMapping(): array
    {
        return [
            'title'      => function (Incident $incident) { return $incident->getTitle(); },
            'info'       => function (Incident $incident) { return json_encode($incident->getInfo(), JSON_THROW_ON_ERROR); },
            'date'       => function (Incident $incident) { return $incident->getDate(); },
            'created_at' => function (Incident $incident) { return $incident->getCreatedAt(); },
            'updated_at' => function (Incident $incident) { return $incident->getUpdatedAt(); },
            'deleted'    => function (Incident $incident) { return $incident->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'type'              => [
                'target_eav_type_alias' => 'incident_type',
                'getter_callback'       => function (Incident $incident) { return [ $incident->getType() ]; }
            ],
            'status'            => [
                'target_eav_type_alias' => 'incident_status',
                'getter_callback'       => function (Incident $incident) { return [ $incident->getStatus() ]; }
            ],
            'created_by'        => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Incident $incident) { return [ $incident->getCreatedBy() ]; }
            ],
            'updated_by'        => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Incident $incident) { return [ $incident->getUpdatedBy() ]; }
            ],
            'action'            => [
                'target_eav_type_alias' => 'action',
                'getter_callback'       => function (Incident $incident) { return $incident->getActions()->getValues(); }
            ],
            'responsible_group' => [
                'target_eav_type_alias' => 'group',
                'getter_callback'       => function (Incident $incident) { return $incident->getResponsibleGroups()->getValues(); }
            ],
            'has_file'          => [
                'target_eav_type_alias' => 'file',
                'getter_callback'       => function (Incident $incident) use ($em) {
                    $fileIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('files')
                        ->select('id')
                        ->where('owner_code = :owner_code')
                        ->where('owner_id = :owner_id')
                        ->setParameter('owner_code', Incident::getOwnerCode())
                        ->setParameter('owner_id', $incident->getId())
                        ->execute()
                        ->fetchAll();

                    $fileIds = array_map(function ($row) { return $row['id']; }, $fileIds);

                    return $em->getRepository(File::class)->findBy([ 'id' => $fileIds ]);
                }
            ]
        ];
    }
}
