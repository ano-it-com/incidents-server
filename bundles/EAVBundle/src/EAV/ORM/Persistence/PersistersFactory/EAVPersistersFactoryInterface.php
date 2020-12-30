<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;

interface EAVPersistersFactoryInterface
{

    public function getForClass(string $class, EAVEntityManagerInterface $em): EAVPersisterInterface;

}