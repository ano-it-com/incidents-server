<?php

namespace App\Infrastructure\Exceptions;

use InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends InvalidArgumentException
{
    private $errors;

    public static function fromConstraintViolationList(ConstraintViolationListInterface $errorsList): self
    {
        $errors = [];
        /** @var ConstraintViolation $error */
        foreach ($errorsList as $error) {
            $errors[$error->getPropertyPath()][] = $error->getMessage();
        }

        return new static($errors);
    }

    public function __construct(array $errors)
    {
        parent::__construct('', 0, null);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}