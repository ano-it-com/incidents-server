<?php


namespace SsoBundle\Services\Auth\Dto;


use DateTimeInterface;

class TokenDTO
{
    /** @var string */
    protected $type;

    /** @var DateTimeInterface */
    protected $expires;

    /** @var string */
    protected $token;

    public function __construct(string $token, string $type, \DateTimeInterface $expires)
    {
        $this->token = $token;
        $this->type = $type;
        $this->expires = $expires;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpires(): DateTimeInterface
    {
        return $this->expires;
    }
}