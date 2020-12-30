<?php

namespace App\ReadModel\ReadModelBuilder\ValueConverter;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

class ValueConverter
{

    /**
     * @var AbstractPlatform
     */
    private $platform;


    public function __construct(EntityManagerInterface $em)
    {
        $this->platform = $em->getConnection()->getDatabasePlatform();
    }


    public function convertToPHPValue($value, string $valueDbType)
    {
        $type = Type::getType($valueDbType);

        return $type->convertToPHPValue($value, $this->platform);
    }
}