<?php

namespace SsoBundle\Infrastructure\Exceptions;

class InternalException extends AbstractSsoException
{

    public function __construct($message)
    {
        parent::__construct($message, 500, null);
    }

    function toArray()
    {
        return [
            'statusCode' => $this->getCode(),
            'message' => $this->message
        ];
    }
}