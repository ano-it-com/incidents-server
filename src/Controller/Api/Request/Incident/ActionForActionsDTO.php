<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class ActionForActionsDTO implements ResolvableInterface
{
    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $actionIds = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();
        $dto->actionIds = $data['actionIds'] ?? [];

        return $dto;
    }
}