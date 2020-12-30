<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

class EAVEntitySettings
{

    private string $tableName;

    private string $entityClass;


    public function __construct(string $tableName, string $entityClass)
    {
        $this->tableName   = $tableName;
        $this->entityClass = $entityClass;
    }


    public function getTableName(): string
    {
        return $this->tableName;
    }


    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}