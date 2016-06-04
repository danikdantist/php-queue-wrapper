<?php
namespace DanikDantist\QueueWrapper;

use DanikDantist\QueueWrapper\Interfaces\IReceivable;

class Manager implements Interfaces\iConsumer, Interfaces\iProducer
{
    protected $connector;

    public function __construct(Drivers\Connector $connector)
    {
        $this->connector = $connector;
    }

    public function addReceiver(IReceivable $receiver)
    {
        $this->connector->addReceiver($receiver);
        return $this;
    }

    public function listenMessage()
    {
        $this->connector->listenMessage();
    }

    public function sendMessage(Message $message)
    {
        $this->connector->sendMessage($message);
    }
}