<?php

namespace App\Infrastructure\Response;

use App\Serializer\Normalizer\DateTimeUTCNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ResponseFactory
{
    public const STATUS_SUCCESS = 200;
    public const STATUS_VALIDATION_ERROR = 400;
    public const STATUS_FORBIDDEN_ERROR = 403;
    public const STATUS_SERVER_ERROR = 500;

    protected static function serialize($data): string
    {
        $normalizers = [new DateTimeUTCNormalizer(), new ObjectNormalizer()];
        $encoder = new JsonEncoder();

        $serializer = new Serializer($normalizers, [$encoder]);
        return $serializer->serialize($data, JsonEncoder::FORMAT);
    }

    public static function success(array $data): JsonResponse
    {
        return new JsonResponse(self::serialize($data), self::STATUS_SUCCESS, [], true);
    }


    public static function validationError(array $data): JsonResponse
    {
        return new JsonResponse(self::serialize($data), self::STATUS_VALIDATION_ERROR, [], true);
    }


    public static function serverError(array $data): JsonResponse
    {
        return new JsonResponse(self::serialize($data), self::STATUS_SERVER_ERROR, [], true);
    }

    public static function forbiddenError(array $data): JsonResponse
    {
        return new JsonResponse(self::serialize($data), self::STATUS_FORBIDDEN_ERROR, [], true);
    }
}