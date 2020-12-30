<?php


namespace SsoBundle\Services\Auth;


use SsoBundle\Services\Auth\Dto\TokenDTO;
use SsoBundle\Services\Auth\Dto\SsoUserDTO;

interface SsoAuthServiceInterface
{
    public function getAuthLink($redirect): string;

    public function auth($code, $redirect): TokenDTO;

    public function getUserData(TokenDTO $token): SsoUserDTO;
}