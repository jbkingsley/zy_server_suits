<?php
namespace DeviceCloudService\Subscribe;

use \GatewayWorker\Lib\Gateway;

class Events {
    /**
     * 消息ID
     *
     * device_state
     * 空闲 1
     * 连接中 2
     * 连接成功 3
     * 采集中 4:
     * 暂停 5:
     * @param $event_data
     */
    static public function _ALTER_COLLECTOR_STATE_($event_data){
        $message = array(
            'messageId' => '',
            'payload' => array(
                'device_id' => $event_data['device_id'],
                'device_state' => $event_data['device_state'],
                'time' => time()
            ),
        );
        Gateway::sendToClient($event_data['client_id'], json_encode($message));
    }

};