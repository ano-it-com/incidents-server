<?php

namespace App\Security;

use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use SsoBundle\Security\InternalAccountManagerInterface;
use SsoBundle\Services\Auth\Dto\SsoUserDTO;
use Symfony\Component\Security\Core\User\UserInterface;

class SsoAccountManager implements InternalAccountManagerInterface
{

    private EntityManagerInterface $em;
    private TokenService $tokenService;

    public function __construct(EntityManagerInterface $em, TokenService $tokenService)
    {
        $this->em = $em;
        $this->tokenService = $tokenService;
    }

    public function upsertUser(SsoUserDTO $userSso): UserInterface
    {
        /** @var User $user */
        if (!($user = $this->getInternalUser($userSso))) {
            $user = (new User())
                ->setLogin($userSso->getId())
                ->setPassword(md5(random_bytes(32)));
            $this->em->persist($user);
        }
        $user->setLastName($userSso->getLastName())
            ->setFirstName($userSso->getFirstName())
            ->setEmail($userSso->getEmail())
            ->setBannedAt($userSso->getBannedAt());

        $this->em->flush();
        return $user;
    }

    public function getInternalUser(SsoUserDTO $userSso): ?UserInterface
    {
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);
        return $repository->findOneByLogin($userSso->getId());
    }

    /**
     * @param User $user
     * @return string
     */
    public function generateToken(UserInterface $user): string
    {
        return $this->tokenService->generateToken($user);
    }
}