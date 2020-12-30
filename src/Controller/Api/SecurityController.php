<?php

namespace App\Controller\Api;

use App\Controller\Api\Request\Security\LoginDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use SsoBundle\Controller\Request\Security\AuthSsoDTO;
use SsoBundle\Services\Auth\SsoAuthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Авторизация
     *
     * @Route("/login", name="app_login", methods={"POST"})
     * @OA\RequestBody(
     *     @Model(type=LoginDTO::class),
     * )
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return JsonResponse
     */
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        return new JsonResponse(['error' => $error], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Ссылки на поддерживаемые вневшие сервисы авторизации
     *
     * @Route("/auth/supports", name="app_auth_supports", methods={"GET"})
     * @param SsoAuthServiceInterface $authService
     * @return JsonResponse
     */
    public function getAuthSupports(SsoAuthServiceInterface $authService): JsonResponse
    {
        $redirectSso = $this->generateUrl('app_auth_sso', [], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse([
            ['type' => 'sso', 'link' => $authService->getAuthLink($redirectSso)],
        ]);
    }

    /**
     * Авторизаци Sso
     * @Route("/auth/sso", name="app_auth_sso", methods={"GET"})
     * @OA\RequestBody(
     *     @Model(type=AuthSsoDTO::class),
     * )
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return JsonResponse
     */
    public function authSso(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        return new JsonResponse(['error' => $error], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Авторизаци Sso [use /auth/supports instead]
     * @deprecated
     * @Route("/auth/sso/link", name="app_auth_link", methods={"GET"})
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return JsonResponse
     */
    public function authSsoLink(SsoAuthServiceInterface $authService): JsonResponse
    {
        $redirectSso = $this->generateUrl('app_auth_sso', [], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(['url' => $authService->getAuthLink($redirectSso),]);
    }
}
