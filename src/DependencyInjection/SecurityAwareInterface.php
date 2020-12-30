<?php

namespace App\DependencyInjection;

use App\Repository\Security\UserRepository;
use App\Security\IncidentAccessViewChecker;

interface SecurityAwareInterface
{
    public function setAccessViewChecker(IncidentAccessViewChecker $accessChecker): void;

    public function setUserRepository(UserRepository $userRepository): void;

    public function getIncidentGrantedUsers(int $incidentId): array;

    public function getActionGrantedUsers(int $actionId): array;
}