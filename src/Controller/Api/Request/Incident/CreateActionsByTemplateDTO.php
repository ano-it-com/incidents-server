<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class CreateActionsByTemplateDTO implements ResolvableInterface
{

    /**
     * @var integer
     * @Assert\NotBlank
     */
    public $templateId;

    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(
     *          ref=@Model(type=CreateActionByTemplateDTO::class )
     *      )
     * )
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="App\Controller\Api\Request\Incident\CreateActionByTemplateDTO",
     *          message="Неверная структура шаблона"
     *      ),
     *     @Assert\NotBlank(),
     * })
     * @Assert\Valid
     */
    public $actions = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();

        $dto->templateId = $data['templateId'] ?? null;

        $actions = $data['actions'] ?? [];
        foreach ($actions as $action) {
            $dto->actions[] = CreateActionByTemplateDTO::fromArray($action);
        }

        return $dto;
    }
}