<?php


namespace App\Serializer\Normalizer;


use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DateTimeUTCNormalizer extends DateTimeNormalizer
{
    public function __construct()
    {
        parent::__construct([
            DateTimeNormalizer::FORMAT_KEY => 'U'
        ]);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return (int)parent::normalize($object, $format, $context);
    }
}