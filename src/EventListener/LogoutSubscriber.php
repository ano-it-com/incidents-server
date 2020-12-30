<?php

namespace App\EventListener;

use App\Infrastructure\Response\ResponseFactory;
use App\Security\Authenticator\TokenAuthenticatorTrait;
use App\Security\TokenService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    use TokenAuthenticatorTrait;

    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function onLogout(LogoutEvent $event): void
    {
        if(!$this->supports($event->getRequest())){
            $event->setResponse(ResponseFactory::forbiddenError([]));
            return;
        }

        if($token = $this->tokenService->getToken($this->getCredentials($event->getRequest()))){
            $this->tokenService->invalidateToken($token);
        }

        $event->setResponse(ResponseFactory::success([]));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout'],
        ];
    }
}
