<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class CreateActionByTemplateDTO implements ResolvableInterface
{
    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public $actionTypeId;

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public $groupId;


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();

        $dto->actionTypeId = $data['actionTypeId'] ?? null;
        $dto->groupId = $data['groupId'] ?? null;

        return $dto;
    }
}