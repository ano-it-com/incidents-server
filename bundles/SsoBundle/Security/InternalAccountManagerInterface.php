<?php


namespace SsoBundle\Security;


use SsoBundle\Services\Auth\Dto\SsoUserDTO;
use Symfony\Component\Security\Core\User\UserInterface;

interface InternalAccountManagerInterface
{
    public function upsertUser(SsoUserDTO $userSso): UserInterface;

    public function generateToken(UserInterface $user): string;
}