<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class ActionWithCommentDTO implements ResolvableInterface
{
    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank(),
     */
    public $comment;

    /**
     * @var integer[]
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $fileIds = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();
        $dto->comment = $data['comment'] ?? null;
        $dto->fileIds = $data['fileIds'] ?? [];

        return $dto;
    }
}