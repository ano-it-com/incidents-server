<?php

namespace App\Controller\Api\Request\Incident;

use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class CreateActionDTO implements ResolvableInterface
{
    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public $typeId;

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $responsibleGroup = [];

    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(
     *          ref=@Model(type=CreateActionTaskDTO::class )
     *      )
     * )
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="App\Controller\Api\Request\Incident\CreateActionTaskDTO",
     *          message="Неверная структура рекомендации"
     *      ),
     * })
     * @Assert\Valid
     */
    public $tasks = [];

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     * })
     */
    public $files = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();
        $dto->typeId = $data['typeId'] ?? null;
        $dto->responsibleGroup = $data['responsibleGroup'] ?? [];
        $dto->files = $data['files'] ?? [];

        $tasks = $data['tasks'] ?? [];

        foreach ($tasks as $task) {
            $dto->tasks[] = CreateActionTaskDTO::fromArray($task);
        }

        return $dto;
    }
}