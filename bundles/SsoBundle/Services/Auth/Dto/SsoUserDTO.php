<?php


namespace SsoBundle\Services\Auth\Dto;

use DateTimeInterface;

class SsoUserDTO
{
    protected $id;

    protected $username = '';

    protected $email = '';

    protected $firstName = '';

    protected $lastName = '';

    protected $roles = [];

    protected $createdAt = null;

    protected $updatedAt = null;

    protected $bannedAt = null;

    protected $isSSOAdmin = false;

    protected $fromLDAP = false;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername($username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function setBannedAt(?DateTimeInterface $bannedAt): self
    {
        $this->bannedAt = $bannedAt;
        return $this;
    }

    public function getBannedAt(): ?DateTimeInterface
    {
        return $this->bannedAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setFromLDAP(bool $fromLDAP): self
    {
        $this->fromLDAP = $fromLDAP;
        return $this;
    }

    public function getFromLDAP(): bool
    {
        return $this->fromLDAP;
    }

    public function setIsSSOAdmin($isSSOAdmin): self
    {
        $this->isSSOAdmin = $isSSOAdmin;
        return $this;
    }

    public function getIsSSOAdmin(): bool
    {
        return $this->isSSOAdmin;
    }
}