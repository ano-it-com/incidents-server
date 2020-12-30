<?php

namespace App\Infrastructure\ArgumentResolvers\Request;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Infrastructure\Exceptions\ValidationException;

class RequestArgumentResolver implements ArgumentValueResolverInterface
{

    /**
     * @var ValidatorInterface
     */
    private $validator;


    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }


    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), ResolvableInterface::class);
    }


    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var ResolvableInterface $dtoClass */
        $dtoClass = $argument->getType();
        try {
            $dto      = $dtoClass::fromRequest($request);
        } catch (\Exception $exception){
            throw new BadRequestException($exception->getMessage(), 400);
        }

        $errors = $this->validator->validate($dto);

        if ($errors->count()) {
            throw ValidationException::fromConstraintViolationList($errors);
        }

        yield $dto;
    }
}