<?php
/**
 * Created by PhpStorm.
 * User: Deby
 * Date: 2016/11/1
 * Time: 下午6:56
 */

use \Workerman\Worker;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// #### 内部推送端口(假设当前服务器内网ip为192.168.100.100) ####
$control_gateway = new Gateway("Text://127.0.0.1:21001");
$control_gateway->name='HandHeldTerminalInternal';

// gateway进程数
$control_gateway->count = 1;
// 本机ip，分布式部署时使用内网ip
$control_gateway->lanIp = '127.0.0.1';

$control_gateway->startPort = 2700;

// 端口为start_register.php中监听的端口
$control_gateway->registerAddress = '127.0.0.1:1218';
// #### 内部推送端口设置完毕 ####

// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
$gateway->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
        /*
        if($_SERVER['HTTP_ORIGIN'] != 'http://kedou.workerman.net')
        {
            $connection->close();
        }*/
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
        // var_dump($_GET, $_SERVER);
    };
};

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}