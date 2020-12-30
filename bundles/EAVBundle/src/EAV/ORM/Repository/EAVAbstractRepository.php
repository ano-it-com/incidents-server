<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

abstract class EAVAbstractRepository
{

    /**
     * @var EAVEntityManagerInterface
     */
    private $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
    {
        $persister = $this->em->getUnitOfWork()->getPersisterForClass($this->getEntityClass());

        return $persister->loadByCriteria($criteria, $orderBy, $limit, $offset);
    }


    abstract public function getEntityClass(): string;


    public function findOneBy(array $criteria, array $orderBy = [])
    {
        $persister = $this->em->getUnitOfWork()->getPersisterForClass($this->getEntityClass());

        $objects = $persister->loadByCriteria($criteria, $orderBy, $limit = 1, $offset = 0);

        return count($objects) ? $objects[0] : null;
    }


    public function find(string $id)
    {
        $persister = $this->em->getUnitOfWork()->getPersisterForClass($this->getEntityClass());

        return $persister->loadById($id);
    }
}