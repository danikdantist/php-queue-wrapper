<?php
namespace DanikDantist\QueueWrapper\Drivers;

use DanikDantist\QueueWrapper\Interfaces;

abstract class Connector implements Interfaces\iConsumer, Interfaces\iProducer
{
    protected $config;

    public function getConfig()
    {
        return $this->config;
    }
}