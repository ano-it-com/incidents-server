<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class CreateActionsForIncidentDTO implements ResolvableInterface
{
    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(
     *          ref=@Model(type=CreateActionDTO::class )
     *      )
     * )
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="App\Controller\Api\Request\Incident\CreateActionDTO",
     *          message="Неверная структура действия"
     *
     *      ),
     *     @Assert\NotBlank(),
     * })
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    public $actions = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $actions = $data['actions'] ?? [];

        foreach ($actions as $action) {
            $dto->actions[] = CreateActionDTO::fromArray($action);
        }

        return $dto;
    }
}