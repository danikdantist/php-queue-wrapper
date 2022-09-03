<?php
namespace DanikDantist\QueueWrapper\Drivers\Kafka;

use DanikDantist\QueueWrapper\Interfaces;
use DanikDantist\QueueWrapper\Interfaces\IReceivable;
use DanikDantist\QueueWrapper\Message;

class Consumer implements Interfaces\iConsumer
{
    protected $consumer;
    protected $isInit = false;
    /** @var Interfaces\iLogable|null  */
    protected $logger = null;
    /** @var IReceivable[]  */
    protected $receiverList = [];

    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
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

    public function setLogger(Interfaces\iLogable $logger)
    {
        $this->logger = $logger;
    }

    protected function init()
    {
        $this->logInfo('Initialize consumer');
        $this->isInit = true;

        $conf = new \RdKafka\Conf();

        $rawConfig = $this->config->getKafkaRawConfig();
        foreach ($rawConfig as $key => $value) {
            $conf->set($key, $value);
        }

        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $this->logInfo('Assign: '.count($partitions));
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $this->logInfo('Revoke:');
                    $kafka->assign(NULL);
                    break;

                default:
                    $this->logError($err);
                    throw new \Exception($err);
            }
        });

        pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
        $conf->set('internal.termination.signal', SIGIO);

        $conf->set('group.id', $this->config->getGroup());
        $conf->set('metadata.broker.list', implode(',', $this->config->getBrokerList()));


        $conf->set("enable.auto.commit", "false");
        $conf->set("enable.auto.offset.store", "false");


        $conf->setErrorCb(function ($kafka, $err, $reason) {
            $this->logError(sprintf("%s (reason: %s)\n", rd_kafka_err2str($err), $reason));
        });


        $consumer = new \RdKafka\KafkaConsumer($conf);


        $consumer->subscribe($this->config->getTopicList());
        $this->consumer = $consumer;
    }

    public function addReceiver(IReceivable $receiver)
    {
        $this->receiverList[] = $receiver;
    }

    public function listenMessage()
    {
        if (!$this->isInit) {
            $this->init();
        }

        $this->logInfo('Waiting for partition assignment...');
        $timeout = $this->config->getTimeout();
        while (true) {
            $message = $this->consumer->consume($timeout);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->logInfo('Receive message, topic-name: "'.$message->topic_name.'", key: "'.$message->key.'", offset: '.$message->offset);
                    foreach ($this->receiverList as $receiver) {
                        $receiver->receiveMessage(new Message($message->payload, $message->topic_name, $message->key, $message->partition));
                    }
                    $this->consumer->commitAsync($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    $this->logInfo('No more messages; will wait for more');
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $this->logInfo('Timed out. Memory usage: '.memory_get_usage(true));
                    break;
                default:
                    $this->logError('offset: '.$message->offset.', key: "'.$message->key.'", topic-name: "'.$message->topic_name.'", error: '.$message->errstr());
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }
}
