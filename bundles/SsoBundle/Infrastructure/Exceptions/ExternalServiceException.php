<?php

namespace SsoBundle\Infrastructure\Exceptions;

class ExternalServiceException extends AbstractSsoException
{
    private $service;

    public function __construct($service, $message)
    {
        parent::__construct($message, 503, null);
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    function toArray()
    {
        return [
            'statusCode' => $this->getCode(),
            'service' => $this->service,
            'message' => $this->message
        ];
    }
}