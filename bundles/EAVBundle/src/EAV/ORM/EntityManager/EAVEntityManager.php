<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettingsFactory;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\EAVUnitOfWork;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\EAVUnitOfWorkInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;
use Doctrine\DBAL\Connection;

class EAVEntityManager implements EAVEntityManagerInterface
{

    private EAVUnitOfWork $uow;

    private Connection $connection;

    private EAVSettings $eavSettings;


    public function __construct(
        Connection $connection,
        EAVPersistersFactoryInterface $persistersFactory,
        EAVSettingsFactory $EAVSettingsFactory
    ) {
        $this->connection  = $connection;
        $this->uow         = new EAVUnitOfWork($this, $persistersFactory);
        $this->eavSettings = $EAVSettingsFactory->make();
    }


    public function getUnitOfWork(): EAVUnitOfWorkInterface
    {
        return $this->uow;

    }


    public function getConnection(): Connection
    {
        return $this->connection;
    }


    public function getEavSettings(): EAVSettings
    {
        return $this->eavSettings;
    }


    public function persist(EAVPersistableInterface $entity): void
    {
        $this->uow->persist($entity);
    }


    public function flush(): void
    {
        $this->uow->commit();
    }


    public function remove($entity): void
    {
        $this->uow->remove($entity);
    }


    public function clear(): void
    {
        $this->uow->clear();
    }
}