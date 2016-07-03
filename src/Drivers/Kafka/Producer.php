<?php
namespace DanikDantist\QueueWrapper\Drivers\Kafka;


use DanikDantist\QueueWrapper\Interfaces;
use DanikDantist\QueueWrapper\Message;

class Producer implements Interfaces\iProducer
{
    protected $isInit = false;
    /** @var \RdKafka\Conf */
    protected $producer;
    protected $config;
    protected $topicMap = [];
    /** @var  Interfaces\iLogable */
    protected $logger;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function setLogger(Interfaces\iLogable $logger)
    {
        $this->logger = $logger;
    }

    protected function logError($error)
    {
        if ($this->logger !== null) {
            $this->logger->error($error);
        }
    }

    protected function logInfo($info)
    {
        if ($this->logger !== null) {
            $this->logger->info($info);
        }
    }

    protected function init()
    {
        $this->logInfo('Initialize producer');
        $this->isInit = true;

        $conf = new \RdKafka\Conf();
        $conf->set('receive.message.max.bytes',1000000);
        $conf->set('topic.metadata.refresh.sparse',true);
        $conf->set('topic.metadata.refresh.interval.ms',600000);
        $conf->set('queued.max.messages.kbytes',100000000);
        $conf->set('socket.send.buffer.bytes',1000000);
        $conf->set('queue.buffering.max.messages',10000000);
        //$conf->set('queue.buffering.max.ms',1);


        $rk = new \RdKafka\Producer($conf);
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers(implode(',', $this->config->getBrokerList()));

        $this->producer = $rk;

    }

    protected function getTopic($topicName)
    {
        if (!$this->isInit) {
            $this->init();
        }

        if (!isset($this->topicMap[$topicName])) {
            $this->logInfo('Prepare topic "'.$topicName.'"');
            $this->topicMap[$topicName] = $this->producer->newTopic($topicName);
        }

        return $this->topicMap[$topicName];
    }

    public function sendMessage(Message $message)
    {
        $topicName = $message->getTopicName();
        $topic = $this->getTopic($topicName);
        $this->logInfo('Send message to topic "'.$topicName.'"');
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message->toString(), $message->getKey());
    }
}