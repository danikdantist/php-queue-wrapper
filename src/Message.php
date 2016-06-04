<?php
namespace DanikDantist\QueueWrapper;

class Message
{
    protected $message = '';
    protected $topicName = '';
    protected $key = '';


    public function __construct($message, $topicName, $key = null, $partition = 0)
    {
        $this->message = $message;
        $this->topicName = $topicName;
        $this->key = $key;
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTopicName()
    {
        return $this->topicName;
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