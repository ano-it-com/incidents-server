<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory;

use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class EAVPersistersLocator
{

    /**
     * @var ServiceLocator
     */
    protected ServiceLocator $persistersLocator;


    public function __construct(ServiceLocator $persistersLocator)
    {
        $this->persistersLocator = $persistersLocator;
    }


    public function getPersisterByClass(string $code): EAVPersisterInterface
    {
        return $this->persistersLocator->get($code);
    }


    public function hasPersisterByClass(string $code): bool
    {
        return $this->persistersLocator->has($code);
    }

}