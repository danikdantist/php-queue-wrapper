<?php
namespace DanikDantist\QueueWrapper\Drivers\Kafka;

use DanikDantist\QueueWrapper\Drivers;
use DanikDantist\QueueWrapper\Interfaces;

use DanikDantist\QueueWrapper\Message;

class Connector extends Drivers\Connector
{
    protected $producer;
    protected $consumer;
    protected $config;
    protected $logger;

    public function __construct(Config $config, Interfaces\iLogable $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    protected function getConsumer(): Interfaces\iConsumer
    {
        if ($this->consumer === null) {
            $this->consumer = new Consumer($this->config);
            if ($this->logger !== null) {
                $this->consumer->setLogger($this->logger);
            }
        }
        return $this->consumer;
    }

    protected function getProducer(): Interfaces\iProducer
    {
        if ($this->producer === null) {
            $this->producer = new Producer($this->config);
            if ($this->logger !== null) {
                $this->producer->setLogger($this->logger);
            }
        }
        return $this->producer;
    }

    public function addReceiver(Interfaces\IReceivable $receiver)
    {
        $this->getConsumer()->addReceiver($receiver);
    }

    public function listenMessage()
    {
        $this->getConsumer()->listenMessage();
    }

    public function sendMessage(Message $message)
    {
        $this->getProducer()->sendMessage($message);
    }

    public function addMessage(Message $message)
    {
        $this->getProducer()->addMessage($message);
    }

    public function flush()
    {
        $this->getProducer()->flush();
    }
}
