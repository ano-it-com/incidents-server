<?php

namespace App\Domain\ActionTask\TypeHandler;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ActionTaskTypeHandler extends AbstractActionTaskTypeHandler
{
    public static function getCode(): string
    {
        return 'handler';
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
    }
}