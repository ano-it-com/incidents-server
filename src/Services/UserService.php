<?php

namespace App\Services;

use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Repository\Security\GroupRepository;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private UserRepository $userRepository;

    private GroupRepository $groupRepository;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRepository,
        GroupRepository $groupRepository,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->em = $em;
    }

    public function createUser(array $userData): User
    {
        $required = ['login', 'email', 'firstName', 'lastName', 'plainPassword'];

        $absentRequiredFields = [];
        foreach ($required as $key) {
            if (!isset($userData[$key])) {
                $absentRequiredFields[] = $key;
            }
        }

        if (!empty($absentRequiredFields)) {
            throw new \RuntimeException(sprintf('Required fields "%s" not exists', implode(', ', $absentRequiredFields)));
        }

        $user = $this->userRepository->findOneByLoginOrEmail($userData['login'], $userData['email']);

        if (null !== $user) {
            throw new \RuntimeException(sprintf('Login %s or email %s already exists', $userData['login'], $userData['email']));
        }

        $user = new User();
        $user
            ->setLogin($userData['login'])
            ->setEmail($userData['email'])
            ->setFirstName($userData['firstName'])
            ->setLastName($userData['lastName'])
            ->setPassword($this->userPasswordEncoder->encodePassword($user, $userData['plainPassword']))
            ->setRoles($userData['roles'] ?? [])
            ->setTelegramId($userData['telegramId'] ?? null);

        $this->em->persist($user);

        return $user;
    }

    public function updateUser(array $userData): User
    {
        $user = $this->userRepository->findOneBy(['id' => $userData['userId']]);

        if (null === $user) {
            throw new \RuntimeException(sprintf('User %s not found', $userData['userId']));
        }

        $user
            ->setLogin($userData['login'] ?? $user->getLogin())
            ->setEmail($userData['email'] ?? $user->getEmail())
            ->setFirstName($userData['firstName'] ?? $user->getFirstName())
            ->setLastName($userData['lastName'] ?? $user->getLastName())
            ->setRoles($userData['roles'] ?? $user->getRoles())
            ->setTelegramId($userData['telegramId'] ?? $user->getTelegramId());

        if (isset($userData['plainPassword'])) {
            $user->setPassword($this->userPasswordEncoder->encodePassword($user, $userData['plainPassword']));
        }

        $this->em->persist($user);

        return $user;
    }

    /**
     * @param User  $user
     * @param array $groupCodes
     *
     * @return Group[]
     */
    public function addUserToGroups(User $user, array $groupCodes): array
    {
        $groups = [];
        if (empty($groupCodes)) {
            $groups = $this->groupRepository->findAll();
        }

        if (!empty($groupCodes)) {
            $groups = $this->groupRepository->findBy(['code' => $groupCodes]);
        }

        if (empty($groups) && empty($groupCodes)) {
            throw new \RuntimeException('Groups not found');
        }

        if (empty($groups) && !empty($groupCodes)) {
            throw new \RuntimeException(sprintf('Selected groups (%s) not found', implode(', ', $groupCodes)));
        }

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $this->em->persist($user);

        return $groups;
    }
}
