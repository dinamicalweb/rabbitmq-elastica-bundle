<?php

namespace Vadiktok\RabbitMQElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Chernoff\DatatableBundle\DependencyInjection
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
                ->arrayNode('producers')
                    ->prototype('array')
                            ->prototype('scalar')
                            ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
