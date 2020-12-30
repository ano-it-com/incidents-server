<?php

namespace ANOITCOM\EAVBundle\Tests\App;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypePropertyRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypePropertyRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\EAVBundle;
use ANOITCOM\EAVBundle\Tests\App\CompilerPass\PublicServicePass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestingKernel extends Kernel
{

    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }


    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineMigrationsBundle(),
            new DoctrineFixturesBundle(),
            new DoctrineBundle(),
            new EAVBundle(),
        ];
    }


    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yaml', 'yaml');
        $loader->load(__DIR__ . '/config/eav.yaml', 'yaml');
    }


    protected function build(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        foreach ($this->servicesToMakePublic() as $service) {
            $container->addCompilerPass(new PublicServicePass('|' . str_replace('\\', '\\\\', $service) . '|'));
        }
    }


    protected function servicesToMakePublic(): array
    {
        return [
            EAVEntityManager::class,
            EAVEntityRepository::class,
            EAVTypeRepository::class,
            EAVEntityRelationTypeRepository::class,
            EAVEntityRelationRepository::class,
            EAVTypeRelationTypeRepository::class,
            EAVTypeRelationRepository::class,
            EAVTypePropertyRelationTypeRepository::class,
            EAVTypePropertyRelationRepository::class,
            ValueTypes::class
        ];
    }

}