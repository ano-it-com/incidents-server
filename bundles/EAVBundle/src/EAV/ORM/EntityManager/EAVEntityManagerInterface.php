<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\EAVUnitOfWorkInterface;
use Doctrine\DBAL\Connection;

interface EAVEntityManagerInterface
{

    public function getUnitOfWork(): EAVUnitOfWorkInterface;


    public function getConnection(): Connection;


    public function getEavSettings(): EAVSettings;


    public function persist(EAVPersistableInterface $entity): void;


    public function flush(): void;


    public function remove($entity): void;


    public function clear(): void;

}