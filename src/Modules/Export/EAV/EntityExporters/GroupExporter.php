<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Security\GroupRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class GroupExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private GroupRepository $groupRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        GroupRepository $groupRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->groupRepository = $groupRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->groupRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'group';
    }

    protected function getFillMapping(): array
    {
        return [
            'title'  => function (Group $group) { return $group->getTitle(); },
            'code'   => function (Group $group) { return $group->getCode(); },
            'public' => function (Group $group) { return $group->getPublic(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        $em = $this->em;

        return [
            'has_user' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (Group $group) use ($em) {
                    $userIds = $this->em
                        ->getConnection()
                        ->createQueryBuilder()
                        ->from('users_groups')
                        ->select('user_id')
                        ->where('group_id = :group_id')
                        ->setParameter('group_id', $group->getId())
                        ->execute()
                        ->fetchAll();

                    $userIds = array_map(function ($row) { return $row['user_id']; }, $userIds);

                    return $em->getRepository(User::class)->findBy([ 'id' => $userIds ]);
                }
            ]
        ];
    }
}
