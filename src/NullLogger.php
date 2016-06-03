<?php
namespace DanikdDntist\QueueWrapper;


class NullLogger implements Interfaces\iLogable
{
    public function info($info)
    {
    }

    public function error($error)
    {
    }
}
