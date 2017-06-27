<?php

use Workerman\Worker;

// 不传参数默认是监听0.0.0.0:2206
$channel_server = new Channel\Server('0.0.0.0', 1111);

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}