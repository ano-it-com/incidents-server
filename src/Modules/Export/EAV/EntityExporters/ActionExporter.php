<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\File\File;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Comment\Comment;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionRepository $actionRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionRepository $actionRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionRepository = $actionRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action';
    }

    protected function getFillMapping(): array
    {
        return [
            'created_at' => function (Action $action) { return $action->getCreatedAt(); },
            'updated_at' => function (Action $action) { return $action->getUpdatedAt(); },
            'deleted'    => function (Action $action) { return $action->getDeleted(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'type'              => [
                'target_eav_type_alias' => 'action_type',
                'getter_callback'       => function (Action $action) { return [ $action->getType() ]; }
            ],
            'status'            => [
                'target_eav_type_alias' => 'action_status',
                'getter_callback'       => function (Action $action) { return [ $action->getStatus() ]; }
            ],
            'created_by'        => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Action $action) { return [ $action->getCreatedBy() ]; }
            ],
            'updated_by'        => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Action $action) { return [ $action->getUpdatedBy() ]; }
            ],
            'action_task'       => [
                'target_eav_type_alias' => 'action_task',
                'getter_callback'       => function (Action $action) { return $action->getActionTasks()->getValues(); }
            ],
            'responsible_group' => [
                'target_eav_type_alias' => 'group',
                'getter_callback'       => function (Action $action) { return [ $action->getResponsibleGroup() ]; }
            ],
            'responsible_user'  => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Action $action) {
                    $user = $action->getResponsibleUser();

                    return $user ? [ $user ] : [];
                }
            ],
            'has_file'          => [
                'target_eav_type_alias' => 'file',
                'getter_callback'       => function (Action $action) use ($em) {
                    $fileIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('files')
                        ->select('id')
                        ->where('owner_code = :owner_code')
                        ->where('owner_id = :owner_id')
                        ->setParameter('owner_code', Action::getOwnerCode())
                        ->setParameter('owner_id', $action->getId())
                        ->execute()
                        ->fetchAll();

                    $fileIds = array_map(function ($row) { return $row['id']; }, $fileIds);

                    return $em->getRepository(File::class)->findBy([ 'id' => $fileIds ]);
                }
            ],
            'comment'           => [
                'target_eav_type_alias' => 'comment',
                'getter_callback'       => function (Action $action) use ($em) {
                    $fileIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('comments')
                        ->select('id')
                        ->where('action_id = :action_id')
                        ->setParameter('action_id', $action->getId())
                        ->execute()
                        ->fetchAll();

                    $fileIds = array_map(function ($row) { return $row['id']; }, $fileIds);

                    return $em->getRepository(Comment::class)->findBy([ 'id' => $fileIds ]);
                }
            ]
        ];
    }
}
