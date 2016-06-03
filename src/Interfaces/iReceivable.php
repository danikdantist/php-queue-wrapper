<?php
namespace DanikdDntist\QueueWrapper\Interfaces;

use DanikdDntist\QueueWrapper;

interface iReceivable
{
    public function receiveMessage(QueueWrapper\Message $message);
}