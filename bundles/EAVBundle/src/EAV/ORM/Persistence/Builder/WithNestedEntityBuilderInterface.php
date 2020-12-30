<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface WithNestedEntityBuilderInterface
{

    public function buildEntities(array $entityRows, array $nestedRows): array;


    public function extractData(EAVPersistableInterface $entity): array;
}