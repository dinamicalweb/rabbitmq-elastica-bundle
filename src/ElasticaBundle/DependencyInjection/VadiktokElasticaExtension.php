<?php

namespace Vadiktok\RabbitMQElasticaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class VadiktokElasticaExtension
 * @package Vadiktok\ElasticaBundle\DependencyInjection
 */
class VadiktokElasticaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.yml');
        $container->setParameter('vadiktok_elastica.queue_name', $config['queue_name']);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'vadiktok_elastica';
    }
}
