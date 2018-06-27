<?php

namespace Vadiktok\RabbitMQElasticaBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class ProducerProvider
{
    /**
     * @var ProducerInterface[]
     */
    protected $producers;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * ProducerProvider constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->producers = [];

        $this->mapping = $mapping;
    }

    /**
     * @param string $producerId
     * @param ProducerInterface $producer
     * @return $this
     */
    public function addProducer(string $producerId, ProducerInterface $producer): ProducerProvider
    {
        $this->producers[$producerId] = $producer;

        return $this;
    }

    /**
     * @param string $indexName
     * @return ProducerInterface
     */
    public function provide(string $indexName): ProducerInterface
    {
        $producer = null;

        foreach ($this->mapping as $producerId => $indexNames) {
            if (empty($indexNames) || in_array($indexName, $indexNames)) {
                $producer = $this->producers[$producerId];
                break;
            }
        }

        if (!$producer) {
            throw new \LogicException(sprintf('Unable to find producer for %s index', $indexName));
        }

        return $producer;
    }
}
