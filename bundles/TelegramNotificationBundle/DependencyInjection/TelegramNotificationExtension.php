<?php

namespace TelegramNotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;


class TelegramNotificationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $container->setParameter($this->getAlias().'.'.$key.
                        '.'.$key2, $value2);
                }
            } else {
                $container->setParameter($this->getAlias().'.'.$key, $value);
            }
        }
    }
}