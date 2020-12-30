<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class CreateIncidentDTO implements ResolvableInterface
{

    /**
     * Название инцидента
     * @var string
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    public $title;

    /**
     * Описание инцидента
     * @var string
     * @Assert\Type("string")
     */
    public $description;

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type("int"),
     */
    public $typeId;

    /**
     * @OA\Property(type="object")
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("array"),
     * })
     */
    public $info = [];

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $responsibleGroups = [];

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
     *      ),
     * })
     * @Assert\Valid
     */
    public $actions = [];

    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(
     *          ref=@Model(type=CreateActionsByTemplateDTO::class )
     *      )
     * )
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="App\Controller\Api\Request\Incident\CreateActionsByTemplateDTO",
     *          message="Неверная структура шаблона"
     *      ),
     * })
     * @Assert\Valid
     */
    public $templates = [];

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     * })
     */
    public $files = [];

    /**
     * @var integer
     * @Assert\Type("int")
     */
    public $repeatedIncidentId;


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $dto->title = $data['title'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->typeId = $data['typeId'] ?? [];
        $dto->info = $data['info'] ?? null;
        $dto->responsibleGroups = $data['responsibleGroups'] ?? [];
        $dto->repeatedIncidentId = $data['repeatedIncidentId'] ?? null;
        $dto->files = $data['files'] ?? [];
        $actions = $data['actions'] ?? [];

        foreach ($actions as $action) {
            $dto->actions[] = CreateActionDTO::fromArray($action);
        }

        $templates = $data['templates'] ?? [];

        foreach ($templates as $template) {
            $dto->templates[] = CreateActionsByTemplateDTO::fromArray($template);
        }

        return $dto;
    }
}