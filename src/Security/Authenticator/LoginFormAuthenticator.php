<?php

namespace App\Security\Authenticator;

use App\Repository\Security\TokenRepository;
use App\Repository\Security\UserRepository;
use App\Security\TokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractGuardAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private TokenService $tokenService;

    private UserPasswordEncoderInterface $passwordEncoder;

    private UserRepository $userRepository;

    private TokenRepository $tokenRepository;

    public function __construct(
        TokenService $tokenService,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        TokenRepository $tokenRepository
    ) {
        $this->tokenService = $tokenService;
        $this->passwordEncoder  = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): array
    {
        $data = json_decode($request->getContent(), true, 2);
        $login = $data['login'] ?? null;
        $password = $data['password'] ?? null;
        if(empty($login) || empty($password)){
            return [];
        }

        return compact('login', 'password');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        if(empty($credentials)){
            throw new CustomUserMessageAuthenticationException('Invalid credentials', [], Response::HTTP_UNAUTHORIZED);
        }

        return $userProvider->loadUserByUsername($credentials['login']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param $credentials
     * @return string|null
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): JsonResponse
    {
        $apiToken = $this->tokenService->generateToken($token->getUser());

        return new JsonResponse(['token' => trim($apiToken)], Response::HTTP_OK);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(['message' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse(['message' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
