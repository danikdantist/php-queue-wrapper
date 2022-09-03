<?php
namespace DanikDantist\QueueWrapper\Interfaces;

interface iLogable
{
    /** @var string $info */
    public function info($info);

    /** @var string $error */
    public function error($error);

    /** @var string $debug */
    public function debug($debug);
}