<?php

namespace ANOITCOM\EAVBundle\DependencyInjection;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettingsFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EAVExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container
            ->getDefinition(EAVSettingsFactory::class)
            ->addArgument($config);


    }
}