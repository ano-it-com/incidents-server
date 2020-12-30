<?php

namespace App\DependencyInjection;

use App\Repository\Security\UserRepository;
use App\Security\IncidentAccessViewChecker;
use Symfony\Component\Security\Core\User\UserInterface;

trait SecurityAwareTrait
{
    private IncidentAccessViewChecker $accessChecker;

    private UserRepository $userRepository;

    public function setAccessViewChecker(IncidentAccessViewChecker $accessChecker): void
    {
        $this->accessChecker = $accessChecker;
    }

    public function setUserRepository(UserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $incidentId
     * @return UserInterface[]
     */
    public function getIncidentGrantedUsers(int $incidentId): array
    {
        $users = $this->userRepository->findAll();

        $grantedUsers = [];
        foreach ($users as $user) {
            if ($this->accessChecker->checkUserAccessToIncident($user, $incidentId)) {
                $grantedUsers[$user->getId()] = $user;
            }
        }

        return $grantedUsers;
    }

    /**
     * @param int $actionId
     * @return UserInterface[]
     */
    public function getActionGrantedUsers(int $actionId): array
    {
        $users = $this->userRepository->findAll();

        $grantedUsers = [];
        foreach ($users as $user) {
            if ($this->accessChecker->checkUserAccessToAction($user, $actionId)) {
                $grantedUsers[$user->getId()] = $user;
            }
        }

        return $grantedUsers;
    }
}