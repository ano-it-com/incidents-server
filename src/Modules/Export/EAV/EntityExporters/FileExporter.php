<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\File\File;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\File\FileRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class FileExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private FileRepository $fileRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        FileRepository $fileRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->fileRepository = $fileRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->fileRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'file';
    }

    protected function getFillMapping(): array
    {
        return [
            'path'          => function (File $file) { return $file->getPath(); },
            'original_name' => function (File $file) { return $file->getOriginalName(); },
            'size'          => function (File $file) { return $file->getSize(); },
            'created_at'    => function (File $file) { return $file->getCreatedAt(); },
            'deleted'       => function (File $file) { return $file->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'created_by' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (File $file) { return [ $file->getCreatedBy() ]; }
            ],
        ];
    }
}
