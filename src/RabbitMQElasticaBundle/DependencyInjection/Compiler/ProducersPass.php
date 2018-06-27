<?php

namespace Vadiktok\RabbitMQElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProducersPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $producerProvider = $container->getDefinition('vadiktok.rabbitmq.elastica.producer_provider');
        foreach ($container->getParameter('vadiktok_elastica.producers') as $producerId => $indexNames) {
            $producer = new Reference(sprintf('old_sound_rabbit_mq.%s_producer', $producerId));

            $producerProvider->addMethodCall('addProducer', [$producerId, $producer]);
        }
    }
}
