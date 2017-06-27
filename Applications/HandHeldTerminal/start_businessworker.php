<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// businessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'HandHelDTerminalBusiness';
// businessWorker进程数量
$worker->count = 2;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:1218';

// 设置处理业务的类为MyEvent
$worker->eventHandler = 'HandHeldTerminalEvents';

//处理进程间通讯事件
$worker->onWorkerStart = function($worker)
{
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('127.0.0.1', 1111);
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}

