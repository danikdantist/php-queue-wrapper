<?php
namespace DanikDantist\QueueWrapper\Drivers\Kafka;
use DanikDantist\QueueWrapper\Drivers;

class Config extends Drivers\Config
{
    /** @var string  */
    protected $group = '';
    /** @var string[]  */
    protected $brokerList = [];
    /** @var string[]  */
    protected $topicList = [];
    /** @var int timeout ms */
    protected $timeout = 120000;

    /*1..7*/
    protected $rdKafkaLogLvl = null;
    protected $kafkaDebug = null;

    protected $rawRdKafkaConfig = [
    ];

    public function setKafkaLogLvl($lvl)
    {
        $this->rdKafkaLogLvl = $lvl;
        return $this;
    }

    public function getKafkaLogLvl()
    {
        return $this->rdKafkaLogLvl;
    }

    /**
     *   generic, broker, topic, metadata, queue, msg, protocol, cgrp, security, fetch, feature, all
     */
    public function setKafkaDebug($debug)
    {
        $this->kafkaDebug = $debug;
        return $this;
    }

    public function getKafkaDebug()
    {
        return $this->kafkaDebug;
    }


    public function setKafkaRawConfig(array $params)
    {
        $this->rawRdKafkaConfig = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getKafkaRawConfig()
    {
        return $this->rawRdKafkaConfig;
    }

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
