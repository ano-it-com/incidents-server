<?php

namespace App\Controller\Api\Request\Incident;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use OpenApi\Annotations as OA;

class UpdateActionTaskReportDTO implements ResolvableInterface
{
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
    public $filesReport = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();
        $dto->reportData   = $data['reportData'] ?? null;
        $dto->filesReport  = $data['filesReport'] ?? null;

        return $dto;
    }
}