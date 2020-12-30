<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface SimpleEntityBuilderInterface
{

    public function buildEntities(array $entityRows): array;


    public function extractData(EAVPersistableInterface $entity): array;
}