<?php

namespace SsoBundle\Infrastructure\Exceptions;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends AbstractSsoException
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
        parent::__construct('', 400, null);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    function toArray()
    {
        return [
            'statusCode' => $this->getCode(),
            'errors' => $this->errors
        ];
    }
}