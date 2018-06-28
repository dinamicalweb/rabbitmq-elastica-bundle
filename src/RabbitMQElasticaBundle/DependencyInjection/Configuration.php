<?php

namespace Vadiktok\RabbitMQElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Vadiktok\RabbitMQElasticaBundle\QueuePagerPersister;

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
                ->enumNode('order')
                    ->values([QueuePagerPersister::ORDER_ASC, QueuePagerPersister::ORDER_DESC])
                    ->defaultValue(QueuePagerPersister::ORDER_ASC)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
