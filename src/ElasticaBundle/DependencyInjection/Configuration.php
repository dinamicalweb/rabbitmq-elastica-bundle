<?php

namespace Vadiktok\RabbitMQElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Vadiktok\ElasticaBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vadiktok_elastica');

        $rootNode
            ->children()
                ->scalarNode('queue_name')
                    ->defaultValue('vadiktok_elastica')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
