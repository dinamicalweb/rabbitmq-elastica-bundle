<?php

namespace Vadiktok\RabbitMQElasticaBundle;

use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\PostAsyncInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PostPersistEvent;
use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vadiktok\RabbitMQElasticaBundle\RabbitMQ\ProducerProvider;

class QueuePagerPersister implements PagerPersisterInterface
{
    const NAME = 'rabbitmq';
    
    /**
     * @var PersisterRegistry
     */
    private $registry;
    
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ProducerProvider
     */
    private $producerProvider;

    /**
     * QueuePagerPersister constructor.
     * @param PersisterRegistry $registry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(PersisterRegistry $registry, EventDispatcherInterface $dispatcher, ProducerProvider $producerProvider)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
        $this->producerProvider = $producerProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(PagerInterface $pager, array $options = array())
    {
        $producer =$this->producerProvider->provide($options['indexName']);

        $pager->setMaxPerPage(empty($options['max_per_page']) ? 100 : $options['max_per_page']);

        $options = array_replace([
            'max_per_page' => $pager->getMaxPerPage(),
            'first_page' => $pager->getCurrentPage(),
            'last_page' => $pager->getNbPages(),
        ], $options);

        $pager->setCurrentPage($options['first_page']);

        $objectPersister = $this->registry->getPersister($options['indexName'], $options['typeName']);

        try {
            $event = new PrePersistEvent($pager, $objectPersister, $options);
            $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);
            $pager = $event->getPager();
            $options = $event->getOptions();

            $lastPage = min($options['last_page'], $pager->getNbPages());
            $page = $pager->getCurrentPage();
            do {
                $pager->setCurrentPage($page);

                $producer->publish(serialize([
                    $page,
                    $options
                ]));

                $count = $page == $lastPage
                    ? $pager->getNbResults() - (($page - 1) * $pager->getMaxPerPage())
                    : $pager->getMaxPerPage();

                $event = new PostAsyncInsertObjectsEvent($pager, $objectPersister, $count, null, $options);
                $this->dispatcher->dispatch(Events::POST_ASYNC_INSERT_OBJECTS, $event);
                $page++;
            } while ($page <= $lastPage);
        } finally {
            $event = new PostPersistEvent($pager, $objectPersister, $options);
            $this->dispatcher->dispatch(Events::POST_PERSIST, $event);
        }
    }
}
