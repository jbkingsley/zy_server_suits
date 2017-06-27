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

use GatewayWorker\Lib\Gateway;
use CoapProtocol\CoapPdu;

use \InterfaceWorker\Interfaces;
use \InterfaceWorker\Functions;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class DeviceCloudEvents
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
            '_ALTER_COLLECTOR_STATE_',  //切换采集器状态事件
        );
        foreach(self::$worker->subscribes as $event_name) {
            Channel\Client::on($event_name . $businessWorker->id, "\\DeviceCloudService\\Subscribe\\Events::" . $event_name);
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


        /*
        //配置
        DI()->config = new PhalApi_Config_File(API_ROOT . '/Config');

        //调试模式，$_GET['__debug__']可自行改名
        DI()->debug = !empty($_GET['__debug__']) ? true : DI()->config->get('sys.debug');

        //日记纪录
        DI()->logger = new PhalApi_Logger_File(API_ROOT . '/Runtime', PhalApi_Logger::LOG_LEVEL_DEBUG | PhalApi_Logger::LOG_LEVEL_INFO | PhalApi_Logger::LOG_LEVEL_ERROR);

        //数据操作 - 基于NotORM，$_GET['__sql__']可自行改名
        DI()->notorm = new PhalApi_DB_NotORM(DI()->config->get('dbs'), !empty($_GET['__sql__']));

        //翻译语言包设定
        SL('zh_cn');

        /** ---------------- 定制注册 可选服务组件 ---------------- **/

        /**
        //签名验证服务
        DI()->filter = 'PhalApi_Filter_SimpleMD5';
         */

        /**
        //缓存 - Memcache/Memcached
        DI()->cache = function () {
        return new PhalApi_Cache_Memcache(DI()->config->get('sys.mc'));
        };
         */

        /**
        //支持JsonP的返回
        if (!empty($_GET['callback'])) {
        DI()->response = new PhalApi_Response_JsonP($_GET['callback']);
        }
         */
        // 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login");
    }

   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param string|NULL $message 具体消息
    * @throws
    */
   public static function onMessage($client_id, $message){
       if($message===NULL){
           return;
       }

       self::initRequestData($message);

       Functions::DI()->request = new InterfaceWorker\Request\CoapRequest();
       Functions::DI()->response = new InterfaceWorker\Response\CoapResponse();
       $api = new Interfaces();
       $api->setCurrentClientId($client_id);
       $rs = $api->response();
       $r = $rs->output();

       $pdu= new CoapPdu();
       $pdu->setMessageId($_SERVER['CoAP_MESSAGE_ID']);
       switch($_SERVER['CoAP_TYPE'] ){
           case $pdu::NON :
               break;

           case $pdu::CON :
               $pdu->setType($pdu::ACK);
               if($r===false){
                   $pdu->setCode('4.04');
                   Gateway::sendToCurrentClient($pdu->compile());
               }elseif(is_array($r)){

                   if(isset($r['st']) and $r['st']){
                       $pdu->setCode($r['st']);
                   }
                   if(isset($r['rs']['option'])){
                       $pdu->setPayload(Functions::safe_json_encode($r['rs']['option']));
                   }
                   if(isset($r['rs']['payload'])){
                       $pdu->setPayload(Functions::safe_json_encode($r['rs']['payload']));
                   }
                   Gateway::sendToCurrentClient($pdu->compile());

               }else{
                   var_dump($r);
               }
               break;

           default:
               $pdu->setType( $pdu::ACK );
               $pdu->setCode( '5.00' );
               Gateway::sendToCurrentClient($pdu->compile());
               break;
       }

       return;
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       //GateWay::sendToAll("$client_id logout");

       //销毁readCache
       $readCache = 'ReadCache'.$client_id;
       unset(Functions::DI()->$readCache);

       if (isset($_SESSION['deviceId'])) {
           $clientsUid = Functions::DI()->clientsUid;
           if(isset($clientsUid[$_SESSION['deviceId']])){
               unset($clientsUid[$_SESSION['deviceId']]);
               Functions::DI()->clientsUid = $clientsUid;
           }
       }
   }


    /**
     * 将数据处理成Request数据
     * @param string $message 数据对象
     */
   private static function initRequestData($message){
       $pdu = CoapPdu::fromBinString($message);
       // Init Server Data
       $_SERVER['CoAP_VERSION']    =  $pdu->version;
       $_SERVER['CoAP_TYPE']       =  $pdu->type;
       $_SERVER['CoAP_CODE']       =  $pdu->code;
       $_SERVER['CoAP_MESSAGE_ID'] =  $pdu->messageId;
       $_SERVER['CoAP_TOKEN']      =  $pdu->token;

       // Init Post Get Request Data
       $_POST = $_GET = array();
       $tmpUri = array();
       $tmpQuery = array();
       foreach($pdu->options as $k => $v){
           $optionNumber = $v->getOptionNumber();
           if($optionNumber==11){
               $tmpUri[] = $v->getValue();
           }
           if($optionNumber==15){
               $tmpQuery[] = $v->getValue();
           }
       }
       $_SERVER['REQUEST_URI'] = '/' . implode('/', $tmpUri);
       $_SERVER['QUERY_STRING'] = implode('&', $tmpQuery);

       $_POST = $_GET = array();
       // Parse $_POST.
       if ($pdu->code === $pdu::POST) {
           $_SERVER['REQUEST_METHOD'] = 'POST';
           parse_str($_SERVER['QUERY_STRING'], $_POST);
           $_POST = array_merge($_POST, array( 'service' => implode('.', $tmpUri) ));
           // Payload
           if($pdu->payload){
               $_POST = array_merge($_POST, array('payload' => $pdu->payload));
           }
       }

       // QUERY_STRING
       if ($pdu->code === $pdu::GET) {
           $_SERVER['REQUEST_METHOD'] = 'GET';
           parse_str($_SERVER['QUERY_STRING'], $_GET);
           $_GET = array_merge($_GET, array( 'service' => implode('.', $tmpUri) ));
           // Payload
           if($pdu->payload){
               $_GET = array_merge($_GET, array('payload' => $pdu->payload));
           }
       }

       $_REQUEST = array_merge($_POST, $_GET);

       return;
   }
}
