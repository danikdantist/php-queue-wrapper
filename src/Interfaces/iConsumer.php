<?php
namespace DanikDantist\QueueWrapper\Interfaces;

interface iConsumer
{
    public function addReceiver(IReceivable $receiver);

    public function listenMessage();
}