<?php

namespace App\Controller\Api\Request;

use App\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ListParamsDTO implements ResolvableInterface
{
    /**
     * @var string
     * @Assert\Type("string")
     */
    public $sortField = 'id';

    /**
     * @var string
     * @Assert\Type("string")
     */
    public $sortDir = 'asc';

    /**
     * @var integer
     * @Assert\Type("int")
     */
    public $perPage = 10;

    /**
     * @var integer
     * @Assert\Type("int")
     */
    public $page = 1;

    /**
     * @var string[]
     * @Assert\Type("array")
     */
    public $filters = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $dto = new self();

        $data = $request->query->all();

        $dto->sortField = $data['sort']['field'] ?? $dto->sortField;
        $dto->sortDir   = $data['sort']['dir'] ?? $dto->sortDir;
        $dto->perPage   = isset($data['perPage']) ? (int)$data['perPage'] : $dto->perPage;
        $dto->page      = isset($data['page']) ? (int)$data['page'] : $dto->page;
        $dto->filters   = $data['filter'] ?? $dto->filters;

        return $dto;
    }
}