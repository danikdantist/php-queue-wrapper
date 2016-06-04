<?php
namespace DanikDantist\QueueWrapper\Interfaces;

use DanikDantist\QueueWrapper\Message;

interface iProducer
{
    public function sendMessage(Message $message);
}