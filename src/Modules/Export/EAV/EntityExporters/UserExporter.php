<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Entity\Security\User;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Security\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private UserRepository $userRepository;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->userRepository = $userRepository;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->userRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'user';
    }

    protected function getFillMapping(): array
    {
        return [
            'login'       => function (User $user) { return $user->getLogin(); },
            'email'       => function (User $user) { return $user->getEmail(); },
            'first_name'  => function (User $user) { return $user->getFirstName(); },
            'last_name'   => function (User $user) { return $user->getLastName(); },
            'telegram_id' => function (User $user) { return $user->getTelegramId(); },

        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'belongs_to_group' => [
                'target_eav_type_alias' => 'group',
                'getter_callback'       => function (User $user) { return $user->getGroups()->getValues(); }
            ]
        ];
    }
}
