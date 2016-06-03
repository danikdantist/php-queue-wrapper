<?php
namespace DanikdDntist\QueueWrapper;

class ReceiverDemon
{
    protected $config;
    protected $receiver;
    protected $isInit = false;
    protected $logger;
    protected $consumer;

    public function __construct(Interfaces\iReceivable $receiver, Conf\Config $config, Interfaces\iLogable $logger = null)
    {
        $this->receiver = $receiver;
        $this->config = $config;

        if ($logger === null) {
            $this->logger = new NullLogger();
        } else {
            $this->logger = $logger;
        }
    }

    public function setLogger(Interfaces\iLogable $logger)
    {
        $this->logger = $logger;
    }

    protected function init()
    {
        $this->isInit = true;

        $conf = new \RdKafka\Conf();

        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $this->logger->info('Assign:');
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $this->logger->info('Revoke:');
                    $kafka->assign(NULL);
                    break;

                default:
                    $this->logger->error($err);
                    throw new \Exception($err);
            }
        });


        $conf->set('group.id', $this->config->getGroup());
        $conf->set('metadata.broker.list', implode(',', $this->config->getBrokerList()));
        $conf->set('receive.message.max.bytes',1024*1024*100);

        $topicConf = new \RdKafka\TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('offset.store.method', 'broker');
        $conf->setDefaultTopicConf($topicConf);

        $consumer = new \RdKafka\KafkaConsumer($conf);


        $consumer->subscribe($this->config->getTopicList());
        $this->consumer = $consumer;
    }

    public function start()
    {
        if (!$this->isInit) {
            $this->init();
        }

        $this->logger->info('Waiting for partition assignment...');
        $timeout = $this->config->getTimeout();
        while (true) {
            $message = $this->consumer->consume($timeout);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->logger->info('Receive message, key: '.$message->key.', offset: '.$message->offset);
                    $this->receiver->receiveMessage(new Message($message->payload, $message->topic_name, $message->partition, $message->key));
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    $this->logger->info('No more messages; will wait for more');
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $this->logger->info('Timed out');
                    $this->logger->info('Memory usage: '.memory_get_usage(true));
                    break;
                default:
                    $this->logger->error('offset: '.$message->offset.', key: '.$message->key.' error: '.$message->errstr());
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }
}