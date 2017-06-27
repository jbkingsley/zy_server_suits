<?php
namespace HandHeldTerminal\Events;

use \InterfaceWorker\Api;
use \InterfaceWorker\Functions;

use HandHeldTerminal\Subscribe;
use Channel;

class CaptureData extends Api{

    public function getRules() {
        return array(
            'connectToCollector' => array(
                'device_id' => array('name' => 'device_id', 'type' => 'char', 'require' => true, 'desc' => '设备ID'),
            )
        );
    }

    /**
     * 连接采集器
     * @desc 采集数据时,需要先连接采集器
     */
    function connectingToCollector() {
        $device_id = Functions::DI()->request->get('device_id');

        //publish一个连接采集器消息
        $event_name = '_ALTER_COLLECTOR_STATE_';
        $event_data = array('device_id'=>$device_id, 'client_id'=>Functions::DI()->currentClientId);
        Channel\Client::publish($event_name, $event_data );


        $response = Functions::DI()->response;
        $response->setPayload(array('deviceState'=>3, 'deviceStateTime'=>time(), 'deviceId'=>12345));
        $response->setMessageId(2101);
        return $response->ST_OK();

    }

    /**
     * 通知采集器开始采集
     * @desc 采集数据时,需要先连接采集器
     */
    function collectorStart() {
        $device_id = Functions::DI()->request->get('device_id');
        $device_state = Functions::DI()->request->get('device_state');

        //publish一个连接采集器消息
        $event_name = '_ALTER_COLLECTOR_STATE_';
        $event_data = array('client_id'=>Functions::DI()->currentClientId, 'device_id'=>$device_id, 'device_state'=>$device_state);
        Channel\Client::publish($event_name, $event_data);

        $response = Functions::DI()->response;
        $response->setPayload(array('device_state'=> 4, 'deviceStateTime'=>time(), 'deviceId'=>12345));
        $response->setMessageId(2102);
        return $response->ST_OK();
    }

    /**
     * 通知采集器开始采集
     * @desc 采集数据时,需要先连接采集器
     */
    function collectorStop() {
        $device_id = Functions::DI()->request->get('device_id');
        $device_state = Functions::DI()->request->get('device_state');

        //publish一个连接采集器消息
        $event_name = '_ALTER_COLLECTOR_STATE_';
        $event_data = array('client_id'=>Functions::DI()->currentClientId, 'device_id'=>$device_id, 'device_state'=>$device_state);
        Channel\Client::publish($event_name, $event_data);

        $response = Functions::DI()->response;
        $response->setPayload(array('device_state'=> 5, 'deviceStateTime'=>time(), 'deviceId'=>12345));
        $response->setMessageId(2103);
        return $response->ST_OK();
    }

    /**
     * 通知采集器开始采集
     * @desc 采集数据时,需要先连接采集器
     */
    function getCapturedData() {
        $device_id = Functions::DI()->request->get('device_id');
        $device_state = Functions::DI()->request->get('device_state');

        //publish一个连接采集器消息
        $event_name = '_ALTER_COLLECTOR_STATE_';
        $event_data = array('client_id'=>Functions::DI()->currentClientId, 'device_id'=>$device_id, 'device_state'=>$device_state);
        Channel\Client::publish($event_name, $event_data);

        $response = Functions::DI()->response;
        $response->setPayload(array('device_state'=> 5, 'deviceStateTime'=>time(), 'deviceId'=>12345));
        $response->setMessageId(2105);
        return $response->ST_OK();
    }

}