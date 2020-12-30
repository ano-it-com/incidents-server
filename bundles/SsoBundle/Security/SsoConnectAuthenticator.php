<?php

namespace SsoBundle\Security;


use SsoBundle\Services\Auth\SsoAuthServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SsoConnectAuthenticator extends AbstractGuardAuthenticator
{
    public const LOGIN_ROUTE = 'app_auth_sso';

    private InternalAccountManagerInterface $accountManager;

    private ValidatorInterface $validator;

    private SsoAuthServiceInterface $authService;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        ValidatorInterface $validator,
        SsoAuthServiceInterface $authService,
        InternalAccountManagerInterface $accountManager,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->accountManager = $accountManager;
        $this->validator = $validator;
        $this->authService = $authService;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('GET');
    }

    public function getCredentials(Request $request)
    {
        return $request->query->get('code');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            return null;
        }

        $redirect = $this->urlGenerator->generate(self::LOGIN_ROUTE, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $token = $this->authService->auth($credentials, $redirect);
        $userData = $this->authService->getUserData($token);
        return $this->accountManager->upsertUser($userData);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    protected function onAuthenticationSuccessResponse(Request $request, TokenInterface $token, string $providerKey, string $apiToken): JsonResponse
    {
        return new JsonResponse(['token' => trim($apiToken)], Response::HTTP_OK);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): JsonResponse
    {
        $apiToken = $this->accountManager->generateToken($token->getUser());
        return $this->onAuthenticationSuccessResponse($request, $token, $providerKey, $apiToken);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            'message' => 'Authentication Sso Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $data = [
            'message' => 'Authentication Sso Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}