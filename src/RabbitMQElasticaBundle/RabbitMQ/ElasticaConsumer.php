<?php

namespace Vadiktok\RabbitMQElasticaBundle\RabbitMQ;

use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\PersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ElasticaConsumer implements ConsumerInterface
{
    /**
     * @var PagerProviderRegistry
     */
    private $pagerProviderRegistry;

    /**
     * @var PersisterRegistry
     */
    private $persisterRegistry;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * ElasticaConsumer constructor.
     * @param PagerProviderRegistry $pagerProviderRegistry
     * @param PersisterRegistry $persisterRegistry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(PagerProviderRegistry $pagerProviderRegistry, PersisterRegistry $persisterRegistry, EventDispatcherInterface $dispatcher)
    {
        $this->pagerProviderRegistry = $pagerProviderRegistry;
        $this->persisterRegistry = $persisterRegistry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message)
    {
        $data = unserialize($message->getBody());
        list($page, $count, $options) = $data;

        $provider = $this->pagerProviderRegistry->getProvider($options['indexName']);
        $pager = $provider->provide($options);

        $objectPersister = $this->persisterRegistry->getPersister($options['indexName']);

        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($options['max_per_page']);

        $event = new PreFetchObjectsEvent($pager, $objectPersister, $options);
        $this->dispatcher->dispatch($event);
        $pager = $event->getPager();
        $options = $event->getOptions();

        $objects = $pager->getCurrentPageResults();

        if ($objects instanceof \Traversable) {
            $objects = iterator_to_array($objects);
        }

        $objects = array_slice($objects, 0, $count);

        $event = new PreInsertObjectsEvent($pager, $objectPersister, $objects, $options);
        $this->dispatcher->dispatch($event);
        $pager = $event->getPager();
        $options = $event->getOptions();
        $objects = $event->getObjects();

        try {
            if (!empty($objects)) {
                $objectPersister->insertMany($objects);
            }

            $event = new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options);
            $this->dispatcher->dispatch($event);
        } catch (\Exception $e) {
            $event = new OnExceptionEvent($pager, $objectPersister, $e, $objects, $options);
            $this->dispatcher->dispatch($event);

            if ($event->isIgnored()) {
                $event = new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options);
                $this->dispatcher->dispatch($event);
                return self::MSG_REJECT;
            }
            return self::MSG_REJECT_REQUEUE;
        }

        return self::MSG_ACK;
    }
}
