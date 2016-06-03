<?php
namespace DanikdDntist\QueueWrapper;

class Message
{
    protected $message = '';

    public function __construct($message, $topicName, $partition, $key)
    {
        $this->message = $message;
    }

    public function toString()
    {
        return $this->message;
    }

    public function __toString()
    {
        return $this->toString();
    }
}