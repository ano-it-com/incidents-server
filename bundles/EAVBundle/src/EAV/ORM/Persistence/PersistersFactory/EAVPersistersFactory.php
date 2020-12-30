<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;

class EAVPersistersFactory implements EAVPersistersFactoryInterface
{

    protected EAVPersistersLocator $persistersLocator;


    public function __construct(EAVPersistersLocator $persistersLocator)
    {
        $this->persistersLocator = $persistersLocator;
    }


    public function getForClass(string $class, EAVEntityManagerInterface $em): EAVPersisterInterface
    {
        if ( ! $this->persistersLocator->hasPersisterByClass($class)) {
            throw new \InvalidArgumentException('Persister for class ' . $class . ' not found!');
        }

        return $this->persistersLocator->getPersisterByClass($class);
    }
}