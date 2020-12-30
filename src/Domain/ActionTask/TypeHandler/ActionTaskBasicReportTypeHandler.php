<?php

namespace App\Domain\ActionTask\TypeHandler;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ActionTaskBasicReportTypeHandler extends AbstractActionTaskTypeHandler
{
    public static function getCode(): string
    {
        return 'basic_report';
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
    }
}