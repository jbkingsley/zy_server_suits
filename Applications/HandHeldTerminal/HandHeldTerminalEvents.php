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

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);
define('ROOT',  __DIR__);
define("TOKEN_VERIFY_CODE",         'uior*(JLU324fewrew');
define("PWD_VERIFY_CODE",           'jfa8Dj435@&lfsdJKFL_S345');
define("SUBSCRIBE_EXPIRY_PERIOD",   3600);

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

use \InterfaceWorker\Interfaces;
use \InterfaceWorker\Functions;



/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class HandHeldTerminalEvents
{

    static $worker;

    public static function onWorkerStart($businessWorker) {
        $container = \DBWorker\Service\Container::getInstance();
        $container->setServices([
            'mysql.adminDB' => [
                'class' => '\DBWorker\Service\Db\Mysql\Adapter',
                'dsn' => 'mysql:host=127.0.0.1;dbname=zy_manage',
                'user' => 'root',
                'password' => 'root',
            ],
        ]);


        self::$worker = $businessWorker;
        self::$worker->clients = array();

        //订阅并注册处理函数
        self::$worker->subscribes = array(
            '_NOTIFY_OF_COLLECTOR_STATE_CHANGE_',  //状态事件
            '_NEW_DATA_CAPTURED_FROM_COLLECTOR_'  //采集事件
        );
        foreach(self::$worker->subscribes as $event_name) {
            Channel\Client::on($event_name . $businessWorker->id, "\\HandHeldTerminal\\Subscribe\\Events::" . $event_name);
        }

        /*
        //定时器
        Timer::add(10, function($businessWorker){
            $time = time();
            foreach($businessWorker->channels as $channel){
                if($channel['time'] < $time){
                    Channel\Client::unsubscribe($channel['event']);
                }
            }
        });*/
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        //为链接设定一个readCache
        $readCache = 'ReadCache'.$client_id;
        Functions::DI()->$readCache = 0;

        //配置
        Functions::DI()->config = new InterfaceWorker\Configs\File(ROOT . '/Config');

        //缓存-Memcache/Memcached
        Functions::DI()->cache = function () {
            return new InterfaceWorker\Caches\Memcached(Functions::DI()->config->get('system.memcache'));
        };

        /*
        //调试模式，$_GET['__debug__']可自行改名
        DI()->debug = !empty($_GET['__debug__']) ? true : DI()->config->get('sys.debug');

        //日记纪录
        DI()->logger = new PhalApi_Logger_File(API_ROOT . '/Runtime', PhalApi_Logger::LOG_LEVEL_DEBUG | PhalApi_Logger::LOG_LEVEL_INFO | PhalApi_Logger::LOG_LEVEL_ERROR);

        //翻译语言包设定
        SL('zh_cn');*/

        // 向当前client_id发送数据 
        //Gateway::sendToClient($client_id, "Hello $client_id\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\n");
    }
    
   /**
    * 当客户端发来消息时触发
    *
    * $message = array(
    *    'service' => 'XXX.XX',
    *    'payload' => array,
    *    'token' => string
    * )
    *
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
       if($message===NULL){
           return;
       }

       $message = Functions::safe_json_decode($message, true);
       var_dump($message);
       if(!isset($message['service'])){
           return;
       }

       $token = isset($message['token']) ? $message['token'] : '';

       switch ($message['service']) {
           case 'main.login':
               //验证TOKEN
               $admin = Functions::DI()->cache->get($token);

               if(empty($admin)){
                   self::sendErrorMessage($client_id);
               }
               else{
                   $adminName = $admin['data']['admin_name'];
                   //解绑已经绑定的admin_id
                   $bind_ids = Gateway::getClientIdByUid($adminName);
                   if (count($bind_ids) > 0) {
                       foreach ($bind_ids as $id)
                           Gateway::unbindUid($id, $adminName);
                   }

                   //绑定新的连接
                   Gateway::bindUid($client_id, $adminName);

                   //保存在线用户
                   $clientsUid = Functions::DI()->clientsUid;
                   $clientsUid[$client_id] = array(
                       'admin' => $admin['data'],
                       "token" => $token
                   );
                   Functions::DI()->clientsUid = $clientsUid;

                   Gateway::sendToCurrentClient(Functions::safe_json_encode(array(
                       'messageId' => 1001,
                       'payload' => array(
                           'isConnecting' => false
                       )
                   )));
               }
           break;

           case 'main.ping':
               return;
               break;

           case 'main.logout':
               unset(Functions::DI()->clientsUid[$client_id]);
               Gateway::closeClient($client_id);
               break;

           case 'bridge.pushByAdminName':
               if(isset($message['toUid']) and isset($message['message'])){
                   Gateway::sendToUid($message['toUid'], $message['message']);
               }
               break;

           default:
               if(isset(Functions::DI()->clientsUid[$client_id])){

                   $clientData = Functions::DI()->clientsUid[$client_id];
                   if(isset($clientData['token']) and $clientData['token']==$token){
                       Functions::DI()->request = new InterfaceWorker\Request\WebSocketRequest($message);
                       Functions::DI()->response = new InterfaceWorker\Response\WebSocketResponse();

                       $api = new Interfaces();
                       $api->setCurrentClientId($client_id);
                       $api->setEventSpace('HandHeldTerminal\\Events\\');
                       $response = $api->response();
                       $r = Functions::safe_json_encode($response->output());

                       Gateway::sendToCurrentClient($r);
                   }

               }else{
                   self::sendErrorMessage($client_id);
               }
           break;
       }

   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       if(isset(Functions::DI()->clientsUid[$client_id])){
           unset(Functions::DI()->clientsUid[$client_id]);
       }
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
   }

   private static function sendErrorMessage($client_id, $error=null){
       Gateway::sendToCurrentClient(Functions::safe_json_encode(array(
           'messageId' => 0,
           'payload' => array(),
           'error' => array(
               'error_code' => isset($error['error_code']) ? $error['error_code'] : '-98',
               'error_message' => isset($error['error_message']) ? $error['error_message'] : '消息不合法',
           )
       )));
       Gateway::closeClient($client_id);
   }
}
