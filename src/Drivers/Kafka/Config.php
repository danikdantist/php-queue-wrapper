<?php
namespace DanikDantist\QueueWrapper\Drivers\Kafka;

class Config
{
    /** @var string  */
    protected $group = '';
    /** @var string[]  */
    protected $brokerList = [];
    /** @var string[]  */
    protected $topicList = [];
    /** @var int timeout ms */
    protected $timeout = 120000;

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return \string[]
     */
    public function getTopicList()
    {
        return $this->topicList;
    }

    /**
     * @param \string $topicName
     * @return $this
     */
    public function addTopic($topicName)
    {
        $this->topicList[] = $topicName;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return array
     */
    public function getBrokerList()
    {
        return $this->brokerList;
    }

    /**
     * @param string $broker - format (host:port)
     * @return $this
     */
    public function addBroker($broker)
    {
        $this->brokerList[] = $broker;
        return $this;
    }
}