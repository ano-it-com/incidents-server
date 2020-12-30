<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

interface EAVRepositoryInterface
{

    public function findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null);


    public function findOneBy(array $criteria, array $orderBy = []);


    public function find(string $id);


    public function getEntityClass(): string;
}