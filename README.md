If you want to use Kafka, you need kafka lib
https://github.com/edenhill/librdkafka
and rdkafka extension for php
https://github.com/arnaud-lb/php-rdkafka

I hope you will help my Dockerfile
https://github.com/danikdantist/Dockerfiles/blob/master/php/php5-fpm-nginx-kafka/Dockerfile

Consumer usage:

```php
<?php

class Receiver implements DanikDantist\QueueWrapper\Interfaces\iReceivable
{
    public function receiveMessage(DanikDantist\QueueWrapper\Message $message)
    {
        echo $message->toString()."\n";
    }
}

class EchoLogger implements DanikDantist\QueueWrapper\Interfaces\iLogable
{
    public function info($info)
    {
        echo 'Info: '.$info."\n";
    }

    public function error($error)
    {
        echo 'Error: '.$error."\n";
    }
}

$config = new DanikDantist\QueueWrapper\Drivers\Kafka\Config();
$config
    ->setGroup('my_group')
    ->addBroker('172.17.0.31:9092')
    ->addTopic('my-test-topic')
    ->addTopic('my-test-topic-2')
;

$demon = new DanikDantist\QueueWrapper\Manager(new DanikDantist\QueueWrapper\Drivers\Kafka\Connector($config, new EchoLogger()));
$demon->addReceiver(new Receiver());
$demon->listenMessage();
```

Producer usage:

```php
<?php

class EchoLogger implements DanikDantist\QueueWrapper\Interfaces\iLogable
{
    public function info($info)
    {
        echo 'Info: '.$info."\n";
    }

    public function error($error)
    {
        echo 'Error: '.$error."\n";
    }
}

$config = new DanikDantist\QueueWrapper\Drivers\Kafka\Config();
$config->addBroker('172.17.0.31:9092');

$demon = new DanikDantist\QueueWrapper\Manager(new DanikDantist\QueueWrapper\Drivers\Kafka\Connector($config, new EchoLogger()));

$demon->sendMessage(new DanikDantist\QueueWrapper\Message('My test message', 'my-test-topic'));
```