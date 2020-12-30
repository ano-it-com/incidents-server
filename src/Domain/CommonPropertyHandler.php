<?php

namespace App\Domain;

class CommonPropertyHandler
{
    protected string $id;

    protected string $name;

    /** @var mixed */
    protected $value;

    protected string $type;

    protected array $prepared = [];

    public function __construct($id, $type, $name, $value)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getPrepared(): array
    {
        return $this->prepared;
    }

    /**
     * @param array $prepared
     */
    public function setPrepared(array $prepared): void
    {
        $this->prepared = $prepared;
    }
}
