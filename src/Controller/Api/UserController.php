<?php

namespace App\Controller\Api;

use App\Entity\Security\User;
use App\Infrastructure\Response\ResponseFactory;
use App\Repository\Security\UserRepository;
use App\Services\FileService;
use App\UserActions\ContextFreeRightsExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private Security $security;

    private FileService $fileService;

    private UserRepository $userRepository;

    public function __construct(FileService $fileService, Security $security, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->fileService = $fileService;
        $this->userRepository = $userRepository;
    }

    /**
     * Получение данных авторизованного пользователя
     *
     * @Route("/user", name="app_user_get", methods={"GET"})
     * @param Security $security
     * @param ContextFreeRightsExporter $rightsExporter
     * @return JsonResponse
     */
    public function getMe(Security $security, ContextFreeRightsExporter $rightsExporter): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        return ResponseFactory::success([
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'rights' => $rightsExporter->export($user)
        ]);
    }
}
