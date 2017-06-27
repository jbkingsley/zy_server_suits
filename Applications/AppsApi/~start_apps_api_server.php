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
require_once ROOT . '/Work/entry.php';

use \Workerman\Worker;
use \Workerman\Protocols;

// 将屏幕打印输出到Worker::$stdoutFile指定的文件中
Worker::$stdoutFile = '/tmp/stdout.log';

$http_worker = new Worker("http://0.0.0.0:2346");
$http_worker->name = 'AppsApiServer';
$http_worker->onConnect = function($connection){
    echo "new connection from ip " . $connection->getRemoteIp() . "\n";
};

$http_worker->onMessage = function($connection, $data){

    Protocols\Http::header('content-type:application:json;charset=utf8');
    Protocols\Http::header('Access-Control-Allow-Origin:*');
    Protocols\Http::header('Access-Control-Allow-Methods:POST, GET, OPTIONS');
    Protocols\Http::header('Access-Control-Allow-Headers:x-requested-with,content-type,x-session-token');

    if(strtoupper($_SERVER['REQUEST_METHOD']) == 'OPTIONS'){
        $connection->send(SJson::encode(array(
            "st" => 1,
            "rs" => 'options'
        )));
    }else{
        SlightPHP::setPathInfo($_SERVER['REQUEST_URI']);
        if(($r=SlightPHP::run())===false){

            Protocols\Http::header('Http-Code', true, 404);
            $connection->send(SJson::encode(array(
                "st" => 0,
                'error_code' => '100404',
                'error_msg'  => STag::map_message_error('100404')
            )));

        }elseif(is_object($r) || is_array($r)){

            if($r['st']===0 and in_array($r['error_code'], array(401, 403, 405, 419, 440))){
                var_dump($r['error_code']);
                Protocols\Http::header('HTTP', true, $r['error_code']);
            }
            $connection->send(SJson::encode($r));

        }else{
            $connection->send($r);
        }
    }
    $connection->close();
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}

