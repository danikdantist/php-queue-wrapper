<?php
namespace DanikDantist\QueueWrapper;

class Message
{
    protected $message = '';
    protected $topicName = '';
    protected $key = '';
    protected $partition = 0;


    public function __construct($message, $topicName, $key = null, $partition = 0)
    {
        $this->message = $message;
        $this->topicName = $topicName;
        $this->key = $key;
        $this->partition = $partition;
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

    /**
     * @return string
     */
    public function getPartition()
    {
        return $this->partition;
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
