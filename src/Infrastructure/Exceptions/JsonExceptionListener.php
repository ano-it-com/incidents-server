<?php

namespace App\Infrastructure\Exceptions;

use App\Infrastructure\Response\ResponseFactory;
use SsoBundle\Infrastructure\Exceptions\AbstractSsoException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class JsonExceptionListener
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $errors = $exception->getErrors();

            $response = ResponseFactory::validationError($errors);
        } elseif ($exception instanceof AbstractSsoException){
            $response = ResponseFactory::validationError($exception->toArray());
        } else {
            if ($this->kernel->isDebug()) {
                return;
            }
            $response = ResponseFactory::serverError([]);
        }

        $event->setResponse($response);

    }
}