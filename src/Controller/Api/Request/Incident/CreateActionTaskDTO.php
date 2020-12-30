<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use OpenApi\Annotations as OA;

class CreateActionTaskDTO implements ResolvableInterface
{

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public $typeId;

    /**
     * @OA\Property(type="object")
     * @Assert\Type("array")
     */
    public $inputData = [];

    /**
     * @OA\Property(type="object")
     * @Assert\Type("array")
     */
    public $reportData = [];

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     * })
     */
    public $filesInput = [];

    /**
     * @var integer[]
     */
    public $filesReport = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();

        $dto->typeId = $data['typeId'] ?? null;
        $dto->inputData = $data['inputData'] ?? [];
        $dto->reportData = $data['reportData'] ?? [];
        $dto->filesInput = $data['filesInput'] ?? [];
        $dto->filesReport = $data['filesReport'] ?? [];

        return $dto;
    }
}