<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\File\File;
use App\Entity\Incident\Comment\Comment;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Comment\CommentRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class CommentExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private CommentRepository $commentRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        CommentRepository $commentRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->commentRepository = $commentRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->commentRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'comment';
    }

    protected function getFillMapping(): array
    {
        return [
            'text'       => function (Comment $comment) { return $comment->getText(); },
            'created_at' => function (Comment $comment) { return $comment->getCreatedAt(); },
            'updated_at' => function (Comment $comment) { return $comment->getUpdatedAt(); },
            'deleted'    => function (Comment $comment) { return $comment->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'has_file' => [
                'target_eav_type_alias' => 'file',
                'getter_callback'       => function (Comment $comment) use ($em) {
                    $fileIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('files')
                        ->select('id')
                        ->where('owner_code = :owner_code')
                        ->where('owner_id = :owner_id')
                        ->setParameter('owner_code', Comment::getOwnerCode())
                        ->setParameter('owner_id', $comment->getId())
                        ->execute()
                        ->fetchAll();

                    $fileIds = array_map(function ($row) { return $row['id']; }, $fileIds);

                    return $em->getRepository(File::class)->findBy([ 'id' => $fileIds ]);
                }
            ]
        ];
    }
}
