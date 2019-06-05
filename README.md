RabbitMQ Elastica Bundle
==============================

As you probably know Elastica populate command is very slow and unstable.

This bundle provides simple functionality to populate [Elastica](https://github.com/FriendsOfSymfony/FOSElasticaBundle) indexes using [RabbitMQ bundle](https://github.com/php-amqplib/RabbitMqBundle)

General idea was taken from [Enqueue Elastica Bundle](https://github.com/php-enqueue/enqueue-elastica-bundle)

### Installation
1)
```
$ composer require vadiktok/rabbitmq-elastica-bundle
```

2) Then, enable the bundle by adding the following line in the app/AppKernel.php file of your project:

```
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Vadiktok\RabbitMQElasticaBundle\VadiktokRabbitMQElasticaBundle(),
        );

        // ...
    }
}
```
3) Define RabbitMQ producer and consumer in your app/config.yml file:

```
old_sound_rabbit_mq:
    producers:
        vadiktok_elastica:
            connection: default
            exchange_options: { name: elastica, type: direct }
            queue_options:    { name: elastica }
    consumers:
        vadiktok_elastica:
            connection: default
            exchange_options: { name: elastica, type: direct }
            queue_options:    { name: elastica }
            callback: vadiktok.rabbitmq.elastica.consumer

```

### Usage

1) Run elastica populate command with --pager-persister option set to "rabbitmq"

```
$ php bin/console fos:elastica:populate --pager-persister=rabbitmq
```

Also you might want to use --max-per-page option. By default it is set to 100 and in my case 20k worked perfectly.

This command simply creates MQ messages with number of pages, amount of results and index name/type.
Consumer takes care about fetching results and pushing into Elasticsearch server.

One page equals one message in queue.
So if you have 1m records and set --max-per-page to 10k -- you will have 100 messages published.

2) Consume your messages with

```
$ php bin/console rabbitmq:consume vadiktok_elastica -vvv
```
The more consumers you run -- the faster your indexes will be populated.
According to my experience reasonable amount of consumers is from 5 to 10.

Also I recommend to use [SupervisorD](http://supervisord.org/) for that.

### Configuration

By default bundle will look at `vadiktok_elastica` producer and publish all messages to it.
But you may also want to modify that name. In this case just add this into your config.yml file:

```
vadiktok_rabbit_mq_elastica:
    producers:
        your_producer_id: ~
```

Or even more. Imagine you want to split your indexes into different queues.
All you have to do is define your consumers/producers same that is described in Installation section
and then tell the bundle which producer to use:

```
vadiktok_rabbit_mq_elastica:
    producers:
        your_producer_id_1: [index_name_1, index_name_2]
        your_producer_id_2: [index_name_3, index_name_4]
```

Now imagine you need your new data first instead of waiting to index data from the first record.
All you have to do is to add "order" parameter:
```
vadiktok_rabbit_mq_elastica:
    order: DESC
```
