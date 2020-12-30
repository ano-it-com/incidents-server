<?php

namespace App\Security\Authenticator;

use App\Security\TokenService;
use Symfony\Component\HttpFoundation\Request;

trait TokenAuthenticatorTrait
{
    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization') &&
            0 === strpos($request->headers->get('Authorization'), TokenService::TOKEN_PREFIX);
    }

    public function getCredentials(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');

        return trim(substr($authorizationHeader, strlen(TokenService::TOKEN_PREFIX)));
    }
}