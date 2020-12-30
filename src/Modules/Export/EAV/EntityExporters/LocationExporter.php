<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Location\Location;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Location\LocationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class LocationExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private LocationRepository $locationRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        LocationRepository $locationRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->locationRepository = $locationRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->locationRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'location';
    }

    protected function getFillMapping(): array
    {
        return [
            'title' => function (Location $location) { return $location->getTitle(); },
            'level' => function (Location $location) { return $location->getLevel(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [];
    }
}
