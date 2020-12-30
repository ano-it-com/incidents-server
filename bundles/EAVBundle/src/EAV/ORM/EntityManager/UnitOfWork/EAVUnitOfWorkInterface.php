<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;

interface EAVUnitOfWorkInterface
{

    public function getPersisterForClass(string $class): EAVPersisterInterface;


    public function registerManaged($entity, array $data): void;


    public function persist(EAVPersistableInterface $entity): void;


    public function commit(): void;


    public function remove(EAVPersistableInterface $entity): void;

}