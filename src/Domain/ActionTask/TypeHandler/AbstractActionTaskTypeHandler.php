<?php

namespace App\Domain\ActionTask\TypeHandler;

use App\Domain\ActionTask\ActionTaskTypeHandlerInterface;
use App\Domain\CommonPropertyHandlerTrait;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractActionTaskTypeHandler implements ActionTaskTypeHandlerInterface
{
    use CommonPropertyHandlerTrait;

    /**
     * Описание
     *
     * @var string
     * @Assert\Type("string")
     */
    public $description;
}