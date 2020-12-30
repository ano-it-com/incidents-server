<?php


namespace App\Tests;


use App\Repository\Security\UserRepository;
use App\Security\TokenService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @mixin KernelBrowser
 */
class KernelBrowserProxy
{
    private KernelBrowser $browser;

    private ContainerInterface $container;

    public function __construct(KernelBrowser $browser, ContainerInterface $container)
    {
        $this->browser = $browser;
        $this->container = $container;
    }

    public function amBearerAuthenticatedByRole(KernelBrowser $client, $role)
    {
        //TODO
    }

    public function amBearerAuthenticatedByLogin($login)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get(UserRepository::class);
        /** @var TokenService $tokenService */
        $tokenService = $this->container->get(TokenService::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['login' => $login]);
        $token = $tokenService->generateToken($testUser);

        // simulate $testUser being logged in
        $this->browser->setServerParameter('HTTP_AUTHORIZATION', $token);
    }

    public function getJsonResponseAsArray(){
        return json_decode($this->browser->getResponse()->getContent(), true);
    }

    public function __call($name, $arguments)
    {
        return $this->browser->$name(...$arguments);
    }

    public function jsonRequest(string $method, string $uri, array $content = [], array $files = [], array $parameters = [],  array $server = [], bool $changeHistory = true){
        $server = array_merge($server, ['CONTENT_TYPE' => 'application/json']);
        $this->browser->request($method,  $uri, $parameters, $files, $server, json_encode($content), $changeHistory);
    }
}