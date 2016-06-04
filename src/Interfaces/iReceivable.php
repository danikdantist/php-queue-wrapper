<?php
namespace DanikDantist\QueueWrapper\Interfaces;

use DanikDantist\QueueWrapper;

interface iReceivable
{
    public function receiveMessage(QueueWrapper\Message $message);
}