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

    protected function logDebug($debug)
    {
        if ($this->logger !== null) {
            $this->logger->debug($debug);
        }
    }

    protected function init()
    {
        $this->logInfo('Initialize producer');
        $this->isInit = true;

        $conf = new \RdKafka\Conf();

        $rawConfig = $this->config->getKafkaRawConfig();
        foreach ($rawConfig as $key => $value) {
            $conf->set($key, $value);
        }

        $conf->setErrorCb(function ($kafka, $err, $reason) {
            $this->logError('broker-list: '.implode(',', $this->config->getBrokerList()));
            $this->logError(sprintf("%s (reason: %s)\n", rd_kafka_err2str($err), $reason));
        });
        if ($this->config->getKafkaLogLvl() !== null) {
            $conf->setLogLevel($this->config->getKafkaLogLvl());
        }
        if ($this->config->getKafkaDebug() !== null) {
            $conf->set('debug', $this->config->getKafkaDebug());
        }
        $conf->set('bootstrap.servers', implode(',', $this->config->getBrokerList()));
        $rk = new \RdKafka\Producer($conf);

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
            $conf = new \RdKafka\TopicConf();

            $this->topicMap[$topicName] = $this->producer->newTopic($topicName, $conf);
        }

        return $this->topicMap[$topicName];
    }

    public function sendMessage(Message $message)
    {
        $topicName = $message->getTopicName();
        $topic = $this->getTopic($topicName);
        $this->logDebug('Send message to topic "'.$topicName.'" '.$message->toString());
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message->toString(), $message->getKey());
        $this->flush();
    }

    public function addMessage(Message $message)
    {
        $topicName = $message->getTopicName();
        $topic = $this->getTopic($topicName);
        $this->logDebug('Send message to topic "'.$topicName.'" '.$message->toString());
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message->toString(), $message->getKey());
        $this->producer->poll(0);
    }

    public function flush()
    {
        $result = null;
        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $this->producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            $this->logError('Was unable to flush, messages might be lost!');
        } else {
            $this->logDebug('Message sent');
        }
    }
}
