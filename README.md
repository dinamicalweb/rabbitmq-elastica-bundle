RabbitMQ Elastica Bundle
==============================

As you probably know Elastica populate command is very slow and unstable.

This bundle provides simple functionality to populate [Elastica](https://github.com/FriendsOfSymfony/FOSElasticaBundle) indexes using [RabbitMQ bundle](https://github.com/php-amqplib/RabbitMqBundle)

General idea was taken from [Enqueue Elastica Bundle](https://github.com/php-enqueue/enqueue-elastica-bundle)

### Installation

```
$ composer require vadiktok/elastica-queue-populate-bundle
```

Then, enable the bundle by adding the following line in the app/AppKernel.php file of your project:

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
            new Vadiktok\ElasticaQueuePopulateBundle\VadiktokElasticaQueuePopulateBundle(),
        );

        // ...
    }
}
```

### Configuration
By default your queue name will be 'vadiktok_elastica'
To set your own -- use configuration (app/config/config.yml):

```
vadiktok_elastica:
    queue_name: your_own_queue_name

```

### Usage

1) Run elastica populate command with --pager-persister option set to "queue"

```
$ php bin/console fos:elastica:populate --pager-persister=queue
```

Also you might want to use --max-per-page option. By default it is set to 100 and in my case 20k worked perfectly.

This command simply creates MQ messages with number of pages, amount of results and index name/type.
Consumer takes care about fetching results and pushing into Elasticsearch server.

One page equals one message in queue.
So if you have 1m records and set --max-per-page to 10k -- you will have 100 messages published.

2) Consume your messages with

```
$ php bin/console rabbitmq:consume elastica -vvv
```
The more consumers you run -- the faster your indexes will be populated.
According to my experience reasonable amount of consumers is from 5 to 10.

Also I recommend to use [SupervisorD](http://supervisord.org/) for that.
