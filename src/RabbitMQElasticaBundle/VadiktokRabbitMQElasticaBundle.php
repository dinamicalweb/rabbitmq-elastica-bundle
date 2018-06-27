<?php

namespace Vadiktok\RabbitMQElasticaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vadiktok\RabbitMQElasticaBundle\DependencyInjection\Compiler\ProducersPass;

class VadiktokRabbitMQElasticaBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProducersPass());
    }
}
